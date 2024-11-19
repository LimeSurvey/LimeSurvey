/**
 * @popperjs/core v2.11.6 - MIT License
 */

(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
  typeof define === 'function' && define.amd ? define(['exports'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.Popper = {}));
}(this, (function (exports) { 'use strict';

  function getWindow(node) {
    if (node == null) {
      return window;
    }

    if (node.toString() !== '[object Window]') {
      var ownerDocument = node.ownerDocument;
      return ownerDocument ? ownerDocument.defaultView || window : window;
    }

    return node;
  }

  function isElement(node) {
    var OwnElement = getWindow(node).Element;
    return node instanceof OwnElement || node instanceof Element;
  }

  function isHTMLElement(node) {
    var OwnElement = getWindow(node).HTMLElement;
    return node instanceof OwnElement || node instanceof HTMLElement;
  }

  function isShadowRoot(node) {
    // IE 11 has no ShadowRoot
    if (typeof ShadowRoot === 'undefined') {
      return false;
    }

    var OwnElement = getWindow(node).ShadowRoot;
    return node instanceof OwnElement || node instanceof ShadowRoot;
  }

  var max = Math.max;
  var min = Math.min;
  var round = Math.round;

  function getUAString() {
    var uaData = navigator.userAgentData;

    if (uaData != null && uaData.brands) {
      return uaData.brands.map(function (item) {
        return item.brand + "/" + item.version;
      }).join(' ');
    }

    return navigator.userAgent;
  }

  function isLayoutViewport() {
    return !/^((?!chrome|android).)*safari/i.test(getUAString());
  }

  function getBoundingClientRect(element, includeScale, isFixedStrategy) {
    if (includeScale === void 0) {
      includeScale = false;
    }

    if (isFixedStrategy === void 0) {
      isFixedStrategy = false;
    }

    var clientRect = element.getBoundingClientRect();
    var scaleX = 1;
    var scaleY = 1;

    if (includeScale && isHTMLElement(element)) {
      scaleX = element.offsetWidth > 0 ? round(clientRect.width) / element.offsetWidth || 1 : 1;
      scaleY = element.offsetHeight > 0 ? round(clientRect.height) / element.offsetHeight || 1 : 1;
    }

    var _ref = isElement(element) ? getWindow(element) : window,
        visualViewport = _ref.visualViewport;

    var addVisualOffsets = !isLayoutViewport() && isFixedStrategy;
    var x = (clientRect.left + (addVisualOffsets && visualViewport ? visualViewport.offsetLeft : 0)) / scaleX;
    var y = (clientRect.top + (addVisualOffsets && visualViewport ? visualViewport.offsetTop : 0)) / scaleY;
    var width = clientRect.width / scaleX;
    var height = clientRect.height / scaleY;
    return {
      width: width,
      height: height,
      top: y,
      right: x + width,
      bottom: y + height,
      left: x,
      x: x,
      y: y
    };
  }

  function getWindowScroll(node) {
    var win = getWindow(node);
    var scrollLeft = win.pageXOffset;
    var scrollTop = win.pageYOffset;
    return {
      scrollLeft: scrollLeft,
      scrollTop: scrollTop
    };
  }

  function getHTMLElementScroll(element) {
    return {
      scrollLeft: element.scrollLeft,
      scrollTop: element.scrollTop
    };
  }

  function getNodeScroll(node) {
    if (node === getWindow(node) || !isHTMLElement(node)) {
      return getWindowScroll(node);
    } else {
      return getHTMLElementScroll(node);
    }
  }

  function getNodeName(element) {
    return element ? (element.nodeName || '').toLowerCase() : null;
  }

  function getDocumentElement(element) {
    // $FlowFixMe[incompatible-return]: assume body is always available
    return ((isElement(element) ? element.ownerDocument : // $FlowFixMe[prop-missing]
    element.document) || window.document).documentElement;
  }

  function getWindowScrollBarX(element) {
    // If <html> has a CSS width greater than the viewport, then this will be
    // incorrect for RTL.
    // Popper 1 is broken in this case and never had a bug report so let's assume
    // it's not an issue. I don't think anyone ever specifies width on <html>
    // anyway.
    // Browsers where the left scrollbar doesn't cause an issue report `0` for
    // this (e.g. Edge 2019, IE11, Safari)
    return getBoundingClientRect(getDocumentElement(element)).left + getWindowScroll(element).scrollLeft;
  }

  function getComputedStyle(element) {
    return getWindow(element).getComputedStyle(element);
  }

  function isScrollParent(element) {
    // Firefox wants us to check `-x` and `-y` variations as well
    var _getComputedStyle = getComputedStyle(element),
        overflow = _getComputedStyle.overflow,
        overflowX = _getComputedStyle.overflowX,
        overflowY = _getComputedStyle.overflowY;

    return /auto|scroll|overlay|hidden/.test(overflow + overflowY + overflowX);
  }

  function isElementScaled(element) {
    var rect = element.getBoundingClientRect();
    var scaleX = round(rect.width) / element.offsetWidth || 1;
    var scaleY = round(rect.height) / element.offsetHeight || 1;
    return scaleX !== 1 || scaleY !== 1;
  } // Returns the composite rect of an element relative to its offsetParent.
  // Composite means it takes into account transforms as well as layout.


  function getCompositeRect(elementOrVirtualElement, offsetParent, isFixed) {
    if (isFixed === void 0) {
      isFixed = false;
    }

    var isOffsetParentAnElement = isHTMLElement(offsetParent);
    var offsetParentIsScaled = isHTMLElement(offsetParent) && isElementScaled(offsetParent);
    var documentElement = getDocumentElement(offsetParent);
    var rect = getBoundingClientRect(elementOrVirtualElement, offsetParentIsScaled, isFixed);
    var scroll = {
      scrollLeft: 0,
      scrollTop: 0
    };
    var offsets = {
      x: 0,
      y: 0
    };

    if (isOffsetParentAnElement || !isOffsetParentAnElement && !isFixed) {
      if (getNodeName(offsetParent) !== 'body' || // https://github.com/popperjs/popper-core/issues/1078
      isScrollParent(documentElement)) {
        scroll = getNodeScroll(offsetParent);
      }

      if (isHTMLElement(offsetParent)) {
        offsets = getBoundingClientRect(offsetParent, true);
        offsets.x += offsetParent.clientLeft;
        offsets.y += offsetParent.clientTop;
      } else if (documentElement) {
        offsets.x = getWindowScrollBarX(documentElement);
      }
    }

    return {
      x: rect.left + scroll.scrollLeft - offsets.x,
      y: rect.top + scroll.scrollTop - offsets.y,
      width: rect.width,
      height: rect.height
    };
  }

  // means it doesn't take into account transforms.

  function getLayoutRect(element) {
    var clientRect = getBoundingClientRect(element); // Use the clientRect sizes if it's not been transformed.
    // Fixes https://github.com/popperjs/popper-core/issues/1223

    var width = element.offsetWidth;
    var height = element.offsetHeight;

    if (Math.abs(clientRect.width - width) <= 1) {
      width = clientRect.width;
    }

    if (Math.abs(clientRect.height - height) <= 1) {
      height = clientRect.height;
    }

    return {
      x: element.offsetLeft,
      y: element.offsetTop,
      width: width,
      height: height
    };
  }

  function getParentNode(element) {
    if (getNodeName(element) === 'html') {
      return element;
    }

    return (// this is a quicker (but less type safe) way to save quite some bytes from the bundle
      // $FlowFixMe[incompatible-return]
      // $FlowFixMe[prop-missing]
      element.assignedSlot || // step into the shadow DOM of the parent of a slotted node
      element.parentNode || ( // DOM Element detected
      isShadowRoot(element) ? element.host : null) || // ShadowRoot detected
      // $FlowFixMe[incompatible-call]: HTMLElement is a Node
      getDocumentElement(element) // fallback

    );
  }

  function getScrollParent(node) {
    if (['html', 'body', '#document'].indexOf(getNodeName(node)) >= 0) {
      // $FlowFixMe[incompatible-return]: assume body is always available
      return node.ownerDocument.body;
    }

    if (isHTMLElement(node) && isScrollParent(node)) {
      return node;
    }

    return getScrollParent(getParentNode(node));
  }

  /*
  given a DOM element, return the list of all scroll parents, up the list of ancesors
  until we get to the top window object. This list is what we attach scroll listeners
  to, because if any of these parent elements scroll, we'll need to re-calculate the
  reference element's position.
  */

  function listScrollParents(element, list) {
    var _element$ownerDocumen;

    if (list === void 0) {
      list = [];
    }

    var scrollParent = getScrollParent(element);
    var isBody = scrollParent === ((_element$ownerDocumen = element.ownerDocument) == null ? void 0 : _element$ownerDocumen.body);
    var win = getWindow(scrollParent);
    var target = isBody ? [win].concat(win.visualViewport || [], isScrollParent(scrollParent) ? scrollParent : []) : scrollParent;
    var updatedList = list.concat(target);
    return isBody ? updatedList : // $FlowFixMe[incompatible-call]: isBody tells us target will be an HTMLElement here
    updatedList.concat(listScrollParents(getParentNode(target)));
  }

  function isTableElement(element) {
    return ['table', 'td', 'th'].indexOf(getNodeName(element)) >= 0;
  }

  function getTrueOffsetParent(element) {
    if (!isHTMLElement(element) || // https://github.com/popperjs/popper-core/issues/837
    getComputedStyle(element).position === 'fixed') {
      return null;
    }

    return element.offsetParent;
  } // `.offsetParent` reports `null` for fixed elements, while absolute elements
  // return the containing block


  function getContainingBlock(element) {
    var isFirefox = /firefox/i.test(getUAString());
    var isIE = /Trident/i.test(getUAString());

    if (isIE && isHTMLElement(element)) {
      // In IE 9, 10 and 11 fixed elements containing block is always established by the viewport
      var elementCss = getComputedStyle(element);

      if (elementCss.position === 'fixed') {
        return null;
      }
    }

    var currentNode = getParentNode(element);

    if (isShadowRoot(currentNode)) {
      currentNode = currentNode.host;
    }

    while (isHTMLElement(currentNode) && ['html', 'body'].indexOf(getNodeName(currentNode)) < 0) {
      var css = getComputedStyle(currentNode); // This is non-exhaustive but covers the most common CSS properties that
      // create a containing block.
      // https://developer.mozilla.org/en-US/docs/Web/CSS/Containing_block#identifying_the_containing_block

      if (css.transform !== 'none' || css.perspective !== 'none' || css.contain === 'paint' || ['transform', 'perspective'].indexOf(css.willChange) !== -1 || isFirefox && css.willChange === 'filter' || isFirefox && css.filter && css.filter !== 'none') {
        return currentNode;
      } else {
        currentNode = currentNode.parentNode;
      }
    }

    return null;
  } // Gets the closest ancestor positioned element. Handles some edge cases,
  // such as table ancestors and cross browser bugs.


  function getOffsetParent(element) {
    var window = getWindow(element);
    var offsetParent = getTrueOffsetParent(element);

    while (offsetParent && isTableElement(offsetParent) && getComputedStyle(offsetParent).position === 'static') {
      offsetParent = getTrueOffsetParent(offsetParent);
    }

    if (offsetParent && (getNodeName(offsetParent) === 'html' || getNodeName(offsetParent) === 'body' && getComputedStyle(offsetParent).position === 'static')) {
      return window;
    }

    return offsetParent || getContainingBlock(element) || window;
  }

  var top = 'top';
  var bottom = 'bottom';
  var right = 'right';
  var left = 'left';
  var auto = 'auto';
  var basePlacements = [top, bottom, right, left];
  var start = 'start';
  var end = 'end';
  var clippingParents = 'clippingParents';
  var viewport = 'viewport';
  var popper = 'popper';
  var reference = 'reference';
  var variationPlacements = /*#__PURE__*/basePlacements.reduce(function (acc, placement) {
    return acc.concat([placement + "-" + start, placement + "-" + end]);
  }, []);
  var placements = /*#__PURE__*/[].concat(basePlacements, [auto]).reduce(function (acc, placement) {
    return acc.concat([placement, placement + "-" + start, placement + "-" + end]);
  }, []); // modifiers that need to read the DOM

  var beforeRead = 'beforeRead';
  var read = 'read';
  var afterRead = 'afterRead'; // pure-logic modifiers

  var beforeMain = 'beforeMain';
  var main = 'main';
  var afterMain = 'afterMain'; // modifier with the purpose to write to the DOM (or write into a framework state)

  var beforeWrite = 'beforeWrite';
  var write = 'write';
  var afterWrite = 'afterWrite';
  var modifierPhases = [beforeRead, read, afterRead, beforeMain, main, afterMain, beforeWrite, write, afterWrite];

  function order(modifiers) {
    var map = new Map();
    var visited = new Set();
    var result = [];
    modifiers.forEach(function (modifier) {
      map.set(modifier.name, modifier);
    }); // On visiting object, check for its dependencies and visit them recursively

    function sort(modifier) {
      visited.add(modifier.name);
      var requires = [].concat(modifier.requires || [], modifier.requiresIfExists || []);
      requires.forEach(function (dep) {
        if (!visited.has(dep)) {
          var depModifier = map.get(dep);

          if (depModifier) {
            sort(depModifier);
          }
        }
      });
      result.push(modifier);
    }

    modifiers.forEach(function (modifier) {
      if (!visited.has(modifier.name)) {
        // check for visited object
        sort(modifier);
      }
    });
    return result;
  }

  function orderModifiers(modifiers) {
    // order based on dependencies
    var orderedModifiers = order(modifiers); // order based on phase

    return modifierPhases.reduce(function (acc, phase) {
      return acc.concat(orderedModifiers.filter(function (modifier) {
        return modifier.phase === phase;
      }));
    }, []);
  }

  function debounce(fn) {
    var pending;
    return function () {
      if (!pending) {
        pending = new Promise(function (resolve) {
          Promise.resolve().then(function () {
            pending = undefined;
            resolve(fn());
          });
        });
      }

      return pending;
    };
  }

  function format(str) {
    for (var _len = arguments.length, args = new Array(_len > 1 ? _len - 1 : 0), _key = 1; _key < _len; _key++) {
      args[_key - 1] = arguments[_key];
    }

    return [].concat(args).reduce(function (p, c) {
      return p.replace(/%s/, c);
    }, str);
  }

  var INVALID_MODIFIER_ERROR = 'Popper: modifier "%s" provided an invalid %s property, expected %s but got %s';
  var MISSING_DEPENDENCY_ERROR = 'Popper: modifier "%s" requires "%s", but "%s" modifier is not available';
  var VALID_PROPERTIES = ['name', 'enabled', 'phase', 'fn', 'effect', 'requires', 'options'];
  function validateModifiers(modifiers) {
    modifiers.forEach(function (modifier) {
      [].concat(Object.keys(modifier), VALID_PROPERTIES) // IE11-compatible replacement for `new Set(iterable)`
      .filter(function (value, index, self) {
        return self.indexOf(value) === index;
      }).forEach(function (key) {
        switch (key) {
          case 'name':
            if (typeof modifier.name !== 'string') {
              console.error(format(INVALID_MODIFIER_ERROR, String(modifier.name), '"name"', '"string"', "\"" + String(modifier.name) + "\""));
            }

            break;

          case 'enabled':
            if (typeof modifier.enabled !== 'boolean') {
              console.error(format(INVALID_MODIFIER_ERROR, modifier.name, '"enabled"', '"boolean"', "\"" + String(modifier.enabled) + "\""));
            }

            break;

          case 'phase':
            if (modifierPhases.indexOf(modifier.phase) < 0) {
              console.error(format(INVALID_MODIFIER_ERROR, modifier.name, '"phase"', "either " + modifierPhases.join(', '), "\"" + String(modifier.phase) + "\""));
            }

            break;

          case 'fn':
            if (typeof modifier.fn !== 'function') {
              console.error(format(INVALID_MODIFIER_ERROR, modifier.name, '"fn"', '"function"', "\"" + String(modifier.fn) + "\""));
            }

            break;

          case 'effect':
            if (modifier.effect != null && typeof modifier.effect !== 'function') {
              console.error(format(INVALID_MODIFIER_ERROR, modifier.name, '"effect"', '"function"', "\"" + String(modifier.fn) + "\""));
            }

            break;

          case 'requires':
            if (modifier.requires != null && !Array.isArray(modifier.requires)) {
              console.error(format(INVALID_MODIFIER_ERROR, modifier.name, '"requires"', '"array"', "\"" + String(modifier.requires) + "\""));
            }

            break;

          case 'requiresIfExists':
            if (!Array.isArray(modifier.requiresIfExists)) {
              console.error(format(INVALID_MODIFIER_ERROR, modifier.name, '"requiresIfExists"', '"array"', "\"" + String(modifier.requiresIfExists) + "\""));
            }

            break;

          case 'options':
          case 'data':
            break;

          default:
            console.error("PopperJS: an invalid property has been provided to the \"" + modifier.name + "\" modifier, valid properties are " + VALID_PROPERTIES.map(function (s) {
              return "\"" + s + "\"";
            }).join(', ') + "; but \"" + key + "\" was provided.");
        }

        modifier.requires && modifier.requires.forEach(function (requirement) {
          if (modifiers.find(function (mod) {
            return mod.name === requirement;
          }) == null) {
            console.error(format(MISSING_DEPENDENCY_ERROR, String(modifier.name), requirement, requirement));
          }
        });
      });
    });
  }

  function uniqueBy(arr, fn) {
    var identifiers = new Set();
    return arr.filter(function (item) {
      var identifier = fn(item);

      if (!identifiers.has(identifier)) {
        identifiers.add(identifier);
        return true;
      }
    });
  }

  function getBasePlacement(placement) {
    return placement.split('-')[0];
  }

  function mergeByName(modifiers) {
    var merged = modifiers.reduce(function (merged, current) {
      var existing = merged[current.name];
      merged[current.name] = existing ? Object.assign({}, existing, current, {
        options: Object.assign({}, existing.options, current.options),
        data: Object.assign({}, existing.data, current.data)
      }) : current;
      return merged;
    }, {}); // IE11 does not support Object.values

    return Object.keys(merged).map(function (key) {
      return merged[key];
    });
  }

  function getViewportRect(element, strategy) {
    var win = getWindow(element);
    var html = getDocumentElement(element);
    var visualViewport = win.visualViewport;
    var width = html.clientWidth;
    var height = html.clientHeight;
    var x = 0;
    var y = 0;

    if (visualViewport) {
      width = visualViewport.width;
      height = visualViewport.height;
      var layoutViewport = isLayoutViewport();

      if (layoutViewport || !layoutViewport && strategy === 'fixed') {
        x = visualViewport.offsetLeft;
        y = visualViewport.offsetTop;
      }
    }

    return {
      width: width,
      height: height,
      x: x + getWindowScrollBarX(element),
      y: y
    };
  }

  // of the `<html>` and `<body>` rect bounds if horizontally scrollable

  function getDocumentRect(element) {
    var _element$ownerDocumen;

    var html = getDocumentElement(element);
    var winScroll = getWindowScroll(element);
    var body = (_element$ownerDocumen = element.ownerDocument) == null ? void 0 : _element$ownerDocumen.body;
    var width = max(html.scrollWidth, html.clientWidth, body ? body.scrollWidth : 0, body ? body.clientWidth : 0);
    var height = max(html.scrollHeight, html.clientHeight, body ? body.scrollHeight : 0, body ? body.clientHeight : 0);
    var x = -winScroll.scrollLeft + getWindowScrollBarX(element);
    var y = -winScroll.scrollTop;

    if (getComputedStyle(body || html).direction === 'rtl') {
      x += max(html.clientWidth, body ? body.clientWidth : 0) - width;
    }

    return {
      width: width,
      height: height,
      x: x,
      y: y
    };
  }

  function contains(parent, child) {
    var rootNode = child.getRootNode && child.getRootNode(); // First, attempt with faster native method

    if (parent.contains(child)) {
      return true;
    } // then fallback to custom implementation with Shadow DOM support
    else if (rootNode && isShadowRoot(rootNode)) {
        var next = child;

        do {
          if (next && parent.isSameNode(next)) {
            return true;
          } // $FlowFixMe[prop-missing]: need a better way to handle this...


          next = next.parentNode || next.host;
        } while (next);
      } // Give up, the result is false


    return false;
  }

  function rectToClientRect(rect) {
    return Object.assign({}, rect, {
      left: rect.x,
      top: rect.y,
      right: rect.x + rect.width,
      bottom: rect.y + rect.height
    });
  }

  function getInnerBoundingClientRect(element, strategy) {
    var rect = getBoundingClientRect(element, false, strategy === 'fixed');
    rect.top = rect.top + element.clientTop;
    rect.left = rect.left + element.clientLeft;
    rect.bottom = rect.top + element.clientHeight;
    rect.right = rect.left + element.clientWidth;
    rect.width = element.clientWidth;
    rect.height = element.clientHeight;
    rect.x = rect.left;
    rect.y = rect.top;
    return rect;
  }

  function getClientRectFromMixedType(element, clippingParent, strategy) {
    return clippingParent === viewport ? rectToClientRect(getViewportRect(element, strategy)) : isElement(clippingParent) ? getInnerBoundingClientRect(clippingParent, strategy) : rectToClientRect(getDocumentRect(getDocumentElement(element)));
  } // A "clipping parent" is an overflowable container with the characteristic of
  // clipping (or hiding) overflowing elements with a position different from
  // `initial`


  function getClippingParents(element) {
    var clippingParents = listScrollParents(getParentNode(element));
    var canEscapeClipping = ['absolute', 'fixed'].indexOf(getComputedStyle(element).position) >= 0;
    var clipperElement = canEscapeClipping && isHTMLElement(element) ? getOffsetParent(element) : element;

    if (!isElement(clipperElement)) {
      return [];
    } // $FlowFixMe[incompatible-return]: https://github.com/facebook/flow/issues/1414


    return clippingParents.filter(function (clippingParent) {
      return isElement(clippingParent) && contains(clippingParent, clipperElement) && getNodeName(clippingParent) !== 'body';
    });
  } // Gets the maximum area that the element is visible in due to any number of
  // clipping parents


  function getClippingRect(element, boundary, rootBoundary, strategy) {
    var mainClippingParents = boundary === 'clippingParents' ? getClippingParents(element) : [].concat(boundary);
    var clippingParents = [].concat(mainClippingParents, [rootBoundary]);
    var firstClippingParent = clippingParents[0];
    var clippingRect = clippingParents.reduce(function (accRect, clippingParent) {
      var rect = getClientRectFromMixedType(element, clippingParent, strategy);
      accRect.top = max(rect.top, accRect.top);
      accRect.right = min(rect.right, accRect.right);
      accRect.bottom = min(rect.bottom, accRect.bottom);
      accRect.left = max(rect.left, accRect.left);
      return accRect;
    }, getClientRectFromMixedType(element, firstClippingParent, strategy));
    clippingRect.width = clippingRect.right - clippingRect.left;
    clippingRect.height = clippingRect.bottom - clippingRect.top;
    clippingRect.x = clippingRect.left;
    clippingRect.y = clippingRect.top;
    return clippingRect;
  }

  function getVariation(placement) {
    return placement.split('-')[1];
  }

  function getMainAxisFromPlacement(placement) {
    return ['top', 'bottom'].indexOf(placement) >= 0 ? 'x' : 'y';
  }

  function computeOffsets(_ref) {
    var reference = _ref.reference,
        element = _ref.element,
        placement = _ref.placement;
    var basePlacement = placement ? getBasePlacement(placement) : null;
    var variation = placement ? getVariation(placement) : null;
    var commonX = reference.x + reference.width / 2 - element.width / 2;
    var commonY = reference.y + reference.height / 2 - element.height / 2;
    var offsets;

    switch (basePlacement) {
      case top:
        offsets = {
          x: commonX,
          y: reference.y - element.height
        };
        break;

      case bottom:
        offsets = {
          x: commonX,
          y: reference.y + reference.height
        };
        break;

      case right:
        offsets = {
          x: reference.x + reference.width,
          y: commonY
        };
        break;

      case left:
        offsets = {
          x: reference.x - element.width,
          y: commonY
        };
        break;

      default:
        offsets = {
          x: reference.x,
          y: reference.y
        };
    }

    var mainAxis = basePlacement ? getMainAxisFromPlacement(basePlacement) : null;

    if (mainAxis != null) {
      var len = mainAxis === 'y' ? 'height' : 'width';

      switch (variation) {
        case start:
          offsets[mainAxis] = offsets[mainAxis] - (reference[len] / 2 - element[len] / 2);
          break;

        case end:
          offsets[mainAxis] = offsets[mainAxis] + (reference[len] / 2 - element[len] / 2);
          break;
      }
    }

    return offsets;
  }

  function getFreshSideObject() {
    return {
      top: 0,
      right: 0,
      bottom: 0,
      left: 0
    };
  }

  function mergePaddingObject(paddingObject) {
    return Object.assign({}, getFreshSideObject(), paddingObject);
  }

  function expandToHashMap(value, keys) {
    return keys.reduce(function (hashMap, key) {
      hashMap[key] = value;
      return hashMap;
    }, {});
  }

  function detectOverflow(state, options) {
    if (options === void 0) {
      options = {};
    }

    var _options = options,
        _options$placement = _options.placement,
        placement = _options$placement === void 0 ? state.placement : _options$placement,
        _options$strategy = _options.strategy,
        strategy = _options$strategy === void 0 ? state.strategy : _options$strategy,
        _options$boundary = _options.boundary,
        boundary = _options$boundary === void 0 ? clippingParents : _options$boundary,
        _options$rootBoundary = _options.rootBoundary,
        rootBoundary = _options$rootBoundary === void 0 ? viewport : _options$rootBoundary,
        _options$elementConte = _options.elementContext,
        elementContext = _options$elementConte === void 0 ? popper : _options$elementConte,
        _options$altBoundary = _options.altBoundary,
        altBoundary = _options$altBoundary === void 0 ? false : _options$altBoundary,
        _options$padding = _options.padding,
        padding = _options$padding === void 0 ? 0 : _options$padding;
    var paddingObject = mergePaddingObject(typeof padding !== 'number' ? padding : expandToHashMap(padding, basePlacements));
    var altContext = elementContext === popper ? reference : popper;
    var popperRect = state.rects.popper;
    var element = state.elements[altBoundary ? altContext : elementContext];
    var clippingClientRect = getClippingRect(isElement(element) ? element : element.contextElement || getDocumentElement(state.elements.popper), boundary, rootBoundary, strategy);
    var referenceClientRect = getBoundingClientRect(state.elements.reference);
    var popperOffsets = computeOffsets({
      reference: referenceClientRect,
      element: popperRect,
      strategy: 'absolute',
      placement: placement
    });
    var popperClientRect = rectToClientRect(Object.assign({}, popperRect, popperOffsets));
    var elementClientRect = elementContext === popper ? popperClientRect : referenceClientRect; // positive = overflowing the clipping rect
    // 0 or negative = within the clipping rect

    var overflowOffsets = {
      top: clippingClientRect.top - elementClientRect.top + paddingObject.top,
      bottom: elementClientRect.bottom - clippingClientRect.bottom + paddingObject.bottom,
      left: clippingClientRect.left - elementClientRect.left + paddingObject.left,
      right: elementClientRect.right - clippingClientRect.right + paddingObject.right
    };
    var offsetData = state.modifiersData.offset; // Offsets can be applied only to the popper element

    if (elementContext === popper && offsetData) {
      var offset = offsetData[placement];
      Object.keys(overflowOffsets).forEach(function (key) {
        var multiply = [right, bottom].indexOf(key) >= 0 ? 1 : -1;
        var axis = [top, bottom].indexOf(key) >= 0 ? 'y' : 'x';
        overflowOffsets[key] += offset[axis] * multiply;
      });
    }

    return overflowOffsets;
  }

  var INVALID_ELEMENT_ERROR = 'Popper: Invalid reference or popper argument provided. They must be either a DOM element or virtual element.';
  var INFINITE_LOOP_ERROR = 'Popper: An infinite loop in the modifiers cycle has been detected! The cycle has been interrupted to prevent a browser crash.';
  var DEFAULT_OPTIONS = {
    placement: 'bottom',
    modifiers: [],
    strategy: 'absolute'
  };

  function areValidElements() {
    for (var _len = arguments.length, args = new Array(_len), _key = 0; _key < _len; _key++) {
      args[_key] = arguments[_key];
    }

    return !args.some(function (element) {
      return !(element && typeof element.getBoundingClientRect === 'function');
    });
  }

  function popperGenerator(generatorOptions) {
    if (generatorOptions === void 0) {
      generatorOptions = {};
    }

    var _generatorOptions = generatorOptions,
        _generatorOptions$def = _generatorOptions.defaultModifiers,
        defaultModifiers = _generatorOptions$def === void 0 ? [] : _generatorOptions$def,
        _generatorOptions$def2 = _generatorOptions.defaultOptions,
        defaultOptions = _generatorOptions$def2 === void 0 ? DEFAULT_OPTIONS : _generatorOptions$def2;
    return function createPopper(reference, popper, options) {
      if (options === void 0) {
        options = defaultOptions;
      }

      var state = {
        placement: 'bottom',
        orderedModifiers: [],
        options: Object.assign({}, DEFAULT_OPTIONS, defaultOptions),
        modifiersData: {},
        elements: {
          reference: reference,
          popper: popper
        },
        attributes: {},
        styles: {}
      };
      var effectCleanupFns = [];
      var isDestroyed = false;
      var instance = {
        state: state,
        setOptions: function setOptions(setOptionsAction) {
          var options = typeof setOptionsAction === 'function' ? setOptionsAction(state.options) : setOptionsAction;
          cleanupModifierEffects();
          state.options = Object.assign({}, defaultOptions, state.options, options);
          state.scrollParents = {
            reference: isElement(reference) ? listScrollParents(reference) : reference.contextElement ? listScrollParents(reference.contextElement) : [],
            popper: listScrollParents(popper)
          }; // Orders the modifiers based on their dependencies and `phase`
          // properties

          var orderedModifiers = orderModifiers(mergeByName([].concat(defaultModifiers, state.options.modifiers))); // Strip out disabled modifiers

          state.orderedModifiers = orderedModifiers.filter(function (m) {
            return m.enabled;
          }); // Validate the provided modifiers so that the consumer will get warned
          // if one of the modifiers is invalid for any reason

          {
            var modifiers = uniqueBy([].concat(orderedModifiers, state.options.modifiers), function (_ref) {
              var name = _ref.name;
              return name;
            });
            validateModifiers(modifiers);

            if (getBasePlacement(state.options.placement) === auto) {
              var flipModifier = state.orderedModifiers.find(function (_ref2) {
                var name = _ref2.name;
                return name === 'flip';
              });

              if (!flipModifier) {
                console.error(['Popper: "auto" placements require the "flip" modifier be', 'present and enabled to work.'].join(' '));
              }
            }

            var _getComputedStyle = getComputedStyle(popper),
                marginTop = _getComputedStyle.marginTop,
                marginRight = _getComputedStyle.marginRight,
                marginBottom = _getComputedStyle.marginBottom,
                marginLeft = _getComputedStyle.marginLeft; // We no longer take into account `margins` on the popper, and it can
            // cause bugs with positioning, so we'll warn the consumer


            if ([marginTop, marginRight, marginBottom, marginLeft].some(function (margin) {
              return parseFloat(margin);
            })) {
              console.warn(['Popper: CSS "margin" styles cannot be used to apply padding', 'between the popper and its reference element or boundary.', 'To replicate margin, use the `offset` modifier, as well as', 'the `padding` option in the `preventOverflow` and `flip`', 'modifiers.'].join(' '));
            }
          }

          runModifierEffects();
          return instance.update();
        },
        // Sync update – it will always be executed, even if not necessary. This
        // is useful for low frequency updates where sync behavior simplifies the
        // logic.
        // For high frequency updates (e.g. `resize` and `scroll` events), always
        // prefer the async Popper#update method
        forceUpdate: function forceUpdate() {
          if (isDestroyed) {
            return;
          }

          var _state$elements = state.elements,
              reference = _state$elements.reference,
              popper = _state$elements.popper; // Don't proceed if `reference` or `popper` are not valid elements
          // anymore

          if (!areValidElements(reference, popper)) {
            {
              console.error(INVALID_ELEMENT_ERROR);
            }

            return;
          } // Store the reference and popper rects to be read by modifiers


          state.rects = {
            reference: getCompositeRect(reference, getOffsetParent(popper), state.options.strategy === 'fixed'),
            popper: getLayoutRect(popper)
          }; // Modifiers have the ability to reset the current update cycle. The
          // most common use case for this is the `flip` modifier changing the
          // placement, which then needs to re-run all the modifiers, because the
          // logic was previously ran for the previous placement and is therefore
          // stale/incorrect

          state.reset = false;
          state.placement = state.options.placement; // On each update cycle, the `modifiersData` property for each modifier
          // is filled with the initial data specified by the modifier. This means
          // it doesn't persist and is fresh on each update.
          // To ensure persistent data, use `${name}#persistent`

          state.orderedModifiers.forEach(function (modifier) {
            return state.modifiersData[modifier.name] = Object.assign({}, modifier.data);
          });
          var __debug_loops__ = 0;

          for (var index = 0; index < state.orderedModifiers.length; index++) {
            {
              __debug_loops__ += 1;

              if (__debug_loops__ > 100) {
                console.error(INFINITE_LOOP_ERROR);
                break;
              }
            }

            if (state.reset === true) {
              state.reset = false;
              index = -1;
              continue;
            }

            var _state$orderedModifie = state.orderedModifiers[index],
                fn = _state$orderedModifie.fn,
                _state$orderedModifie2 = _state$orderedModifie.options,
                _options = _state$orderedModifie2 === void 0 ? {} : _state$orderedModifie2,
                name = _state$orderedModifie.name;

            if (typeof fn === 'function') {
              state = fn({
                state: state,
                options: _options,
                name: name,
                instance: instance
              }) || state;
            }
          }
        },
        // Async and optimistically optimized update – it will not be executed if
        // not necessary (debounced to run at most once-per-tick)
        update: debounce(function () {
          return new Promise(function (resolve) {
            instance.forceUpdate();
            resolve(state);
          });
        }),
        destroy: function destroy() {
          cleanupModifierEffects();
          isDestroyed = true;
        }
      };

      if (!areValidElements(reference, popper)) {
        {
          console.error(INVALID_ELEMENT_ERROR);
        }

        return instance;
      }

      instance.setOptions(options).then(function (state) {
        if (!isDestroyed && options.onFirstUpdate) {
          options.onFirstUpdate(state);
        }
      }); // Modifiers have the ability to execute arbitrary code before the first
      // update cycle runs. They will be executed in the same order as the update
      // cycle. This is useful when a modifier adds some persistent data that
      // other modifiers need to use, but the modifier is run after the dependent
      // one.

      function runModifierEffects() {
        state.orderedModifiers.forEach(function (_ref3) {
          var name = _ref3.name,
              _ref3$options = _ref3.options,
              options = _ref3$options === void 0 ? {} : _ref3$options,
              effect = _ref3.effect;

          if (typeof effect === 'function') {
            var cleanupFn = effect({
              state: state,
              name: name,
              instance: instance,
              options: options
            });

            var noopFn = function noopFn() {};

            effectCleanupFns.push(cleanupFn || noopFn);
          }
        });
      }

      function cleanupModifierEffects() {
        effectCleanupFns.forEach(function (fn) {
          return fn();
        });
        effectCleanupFns = [];
      }

      return instance;
    };
  }

  var passive = {
    passive: true
  };

  function effect$2(_ref) {
    var state = _ref.state,
        instance = _ref.instance,
        options = _ref.options;
    var _options$scroll = options.scroll,
        scroll = _options$scroll === void 0 ? true : _options$scroll,
        _options$resize = options.resize,
        resize = _options$resize === void 0 ? true : _options$resize;
    var window = getWindow(state.elements.popper);
    var scrollParents = [].concat(state.scrollParents.reference, state.scrollParents.popper);

    if (scroll) {
      scrollParents.forEach(function (scrollParent) {
        scrollParent.addEventListener('scroll', instance.update, passive);
      });
    }

    if (resize) {
      window.addEventListener('resize', instance.update, passive);
    }

    return function () {
      if (scroll) {
        scrollParents.forEach(function (scrollParent) {
          scrollParent.removeEventListener('scroll', instance.update, passive);
        });
      }

      if (resize) {
        window.removeEventListener('resize', instance.update, passive);
      }
    };
  } // eslint-disable-next-line import/no-unused-modules


  var eventListeners = {
    name: 'eventListeners',
    enabled: true,
    phase: 'write',
    fn: function fn() {},
    effect: effect$2,
    data: {}
  };

  function popperOffsets(_ref) {
    var state = _ref.state,
        name = _ref.name;
    // Offsets are the actual position the popper needs to have to be
    // properly positioned near its reference element
    // This is the most basic placement, and will be adjusted by
    // the modifiers in the next step
    state.modifiersData[name] = computeOffsets({
      reference: state.rects.reference,
      element: state.rects.popper,
      strategy: 'absolute',
      placement: state.placement
    });
  } // eslint-disable-next-line import/no-unused-modules


  var popperOffsets$1 = {
    name: 'popperOffsets',
    enabled: true,
    phase: 'read',
    fn: popperOffsets,
    data: {}
  };

  var unsetSides = {
    top: 'auto',
    right: 'auto',
    bottom: 'auto',
    left: 'auto'
  }; // Round the offsets to the nearest suitable subpixel based on the DPR.
  // Zooming can change the DPR, but it seems to report a value that will
  // cleanly divide the values into the appropriate subpixels.

  function roundOffsetsByDPR(_ref) {
    var x = _ref.x,
        y = _ref.y;
    var win = window;
    var dpr = win.devicePixelRatio || 1;
    return {
      x: round(x * dpr) / dpr || 0,
      y: round(y * dpr) / dpr || 0
    };
  }

  function mapToStyles(_ref2) {
    var _Object$assign2;

    var popper = _ref2.popper,
        popperRect = _ref2.popperRect,
        placement = _ref2.placement,
        variation = _ref2.variation,
        offsets = _ref2.offsets,
        position = _ref2.position,
        gpuAcceleration = _ref2.gpuAcceleration,
        adaptive = _ref2.adaptive,
        roundOffsets = _ref2.roundOffsets,
        isFixed = _ref2.isFixed;
    var _offsets$x = offsets.x,
        x = _offsets$x === void 0 ? 0 : _offsets$x,
        _offsets$y = offsets.y,
        y = _offsets$y === void 0 ? 0 : _offsets$y;

    var _ref3 = typeof roundOffsets === 'function' ? roundOffsets({
      x: x,
      y: y
    }) : {
      x: x,
      y: y
    };

    x = _ref3.x;
    y = _ref3.y;
    var hasX = offsets.hasOwnProperty('x');
    var hasY = offsets.hasOwnProperty('y');
    var sideX = left;
    var sideY = top;
    var win = window;

    if (adaptive) {
      var offsetParent = getOffsetParent(popper);
      var heightProp = 'clientHeight';
      var widthProp = 'clientWidth';

      if (offsetParent === getWindow(popper)) {
        offsetParent = getDocumentElement(popper);

        if (getComputedStyle(offsetParent).position !== 'static' && position === 'absolute') {
          heightProp = 'scrollHeight';
          widthProp = 'scrollWidth';
        }
      } // $FlowFixMe[incompatible-cast]: force type refinement, we compare offsetParent with window above, but Flow doesn't detect it


      offsetParent = offsetParent;

      if (placement === top || (placement === left || placement === right) && variation === end) {
        sideY = bottom;
        var offsetY = isFixed && offsetParent === win && win.visualViewport ? win.visualViewport.height : // $FlowFixMe[prop-missing]
        offsetParent[heightProp];
        y -= offsetY - popperRect.height;
        y *= gpuAcceleration ? 1 : -1;
      }

      if (placement === left || (placement === top || placement === bottom) && variation === end) {
        sideX = right;
        var offsetX = isFixed && offsetParent === win && win.visualViewport ? win.visualViewport.width : // $FlowFixMe[prop-missing]
        offsetParent[widthProp];
        x -= offsetX - popperRect.width;
        x *= gpuAcceleration ? 1 : -1;
      }
    }

    var commonStyles = Object.assign({
      position: position
    }, adaptive && unsetSides);

    var _ref4 = roundOffsets === true ? roundOffsetsByDPR({
      x: x,
      y: y
    }) : {
      x: x,
      y: y
    };

    x = _ref4.x;
    y = _ref4.y;

    if (gpuAcceleration) {
      var _Object$assign;

      return Object.assign({}, commonStyles, (_Object$assign = {}, _Object$assign[sideY] = hasY ? '0' : '', _Object$assign[sideX] = hasX ? '0' : '', _Object$assign.transform = (win.devicePixelRatio || 1) <= 1 ? "translate(" + x + "px, " + y + "px)" : "translate3d(" + x + "px, " + y + "px, 0)", _Object$assign));
    }

    return Object.assign({}, commonStyles, (_Object$assign2 = {}, _Object$assign2[sideY] = hasY ? y + "px" : '', _Object$assign2[sideX] = hasX ? x + "px" : '', _Object$assign2.transform = '', _Object$assign2));
  }

  function computeStyles(_ref5) {
    var state = _ref5.state,
        options = _ref5.options;
    var _options$gpuAccelerat = options.gpuAcceleration,
        gpuAcceleration = _options$gpuAccelerat === void 0 ? true : _options$gpuAccelerat,
        _options$adaptive = options.adaptive,
        adaptive = _options$adaptive === void 0 ? true : _options$adaptive,
        _options$roundOffsets = options.roundOffsets,
        roundOffsets = _options$roundOffsets === void 0 ? true : _options$roundOffsets;

    {
      var transitionProperty = getComputedStyle(state.elements.popper).transitionProperty || '';

      if (adaptive && ['transform', 'top', 'right', 'bottom', 'left'].some(function (property) {
        return transitionProperty.indexOf(property) >= 0;
      })) {
        console.warn(['Popper: Detected CSS transitions on at least one of the following', 'CSS properties: "transform", "top", "right", "bottom", "left".', '\n\n', 'Disable the "computeStyles" modifier\'s `adaptive` option to allow', 'for smooth transitions, or remove these properties from the CSS', 'transition declaration on the popper element if only transitioning', 'opacity or background-color for example.', '\n\n', 'We recommend using the popper element as a wrapper around an inner', 'element that can have any CSS property transitioned for animations.'].join(' '));
      }
    }

    var commonStyles = {
      placement: getBasePlacement(state.placement),
      variation: getVariation(state.placement),
      popper: state.elements.popper,
      popperRect: state.rects.popper,
      gpuAcceleration: gpuAcceleration,
      isFixed: state.options.strategy === 'fixed'
    };

    if (state.modifiersData.popperOffsets != null) {
      state.styles.popper = Object.assign({}, state.styles.popper, mapToStyles(Object.assign({}, commonStyles, {
        offsets: state.modifiersData.popperOffsets,
        position: state.options.strategy,
        adaptive: adaptive,
        roundOffsets: roundOffsets
      })));
    }

    if (state.modifiersData.arrow != null) {
      state.styles.arrow = Object.assign({}, state.styles.arrow, mapToStyles(Object.assign({}, commonStyles, {
        offsets: state.modifiersData.arrow,
        position: 'absolute',
        adaptive: false,
        roundOffsets: roundOffsets
      })));
    }

    state.attributes.popper = Object.assign({}, state.attributes.popper, {
      'data-popper-placement': state.placement
    });
  } // eslint-disable-next-line import/no-unused-modules


  var computeStyles$1 = {
    name: 'computeStyles',
    enabled: true,
    phase: 'beforeWrite',
    fn: computeStyles,
    data: {}
  };

  // and applies them to the HTMLElements such as popper and arrow

  function applyStyles(_ref) {
    var state = _ref.state;
    Object.keys(state.elements).forEach(function (name) {
      var style = state.styles[name] || {};
      var attributes = state.attributes[name] || {};
      var element = state.elements[name]; // arrow is optional + virtual elements

      if (!isHTMLElement(element) || !getNodeName(element)) {
        return;
      } // Flow doesn't support to extend this property, but it's the most
      // effective way to apply styles to an HTMLElement
      // $FlowFixMe[cannot-write]


      Object.assign(element.style, style);
      Object.keys(attributes).forEach(function (name) {
        var value = attributes[name];

        if (value === false) {
          element.removeAttribute(name);
        } else {
          element.setAttribute(name, value === true ? '' : value);
        }
      });
    });
  }

  function effect$1(_ref2) {
    var state = _ref2.state;
    var initialStyles = {
      popper: {
        position: state.options.strategy,
        left: '0',
        top: '0',
        margin: '0'
      },
      arrow: {
        position: 'absolute'
      },
      reference: {}
    };
    Object.assign(state.elements.popper.style, initialStyles.popper);
    state.styles = initialStyles;

    if (state.elements.arrow) {
      Object.assign(state.elements.arrow.style, initialStyles.arrow);
    }

    return function () {
      Object.keys(state.elements).forEach(function (name) {
        var element = state.elements[name];
        var attributes = state.attributes[name] || {};
        var styleProperties = Object.keys(state.styles.hasOwnProperty(name) ? state.styles[name] : initialStyles[name]); // Set all values to an empty string to unset them

        var style = styleProperties.reduce(function (style, property) {
          style[property] = '';
          return style;
        }, {}); // arrow is optional + virtual elements

        if (!isHTMLElement(element) || !getNodeName(element)) {
          return;
        }

        Object.assign(element.style, style);
        Object.keys(attributes).forEach(function (attribute) {
          element.removeAttribute(attribute);
        });
      });
    };
  } // eslint-disable-next-line import/no-unused-modules


  var applyStyles$1 = {
    name: 'applyStyles',
    enabled: true,
    phase: 'write',
    fn: applyStyles,
    effect: effect$1,
    requires: ['computeStyles']
  };

  function distanceAndSkiddingToXY(placement, rects, offset) {
    var basePlacement = getBasePlacement(placement);
    var invertDistance = [left, top].indexOf(basePlacement) >= 0 ? -1 : 1;

    var _ref = typeof offset === 'function' ? offset(Object.assign({}, rects, {
      placement: placement
    })) : offset,
        skidding = _ref[0],
        distance = _ref[1];

    skidding = skidding || 0;
    distance = (distance || 0) * invertDistance;
    return [left, right].indexOf(basePlacement) >= 0 ? {
      x: distance,
      y: skidding
    } : {
      x: skidding,
      y: distance
    };
  }

  function offset(_ref2) {
    var state = _ref2.state,
        options = _ref2.options,
        name = _ref2.name;
    var _options$offset = options.offset,
        offset = _options$offset === void 0 ? [0, 0] : _options$offset;
    var data = placements.reduce(function (acc, placement) {
      acc[placement] = distanceAndSkiddingToXY(placement, state.rects, offset);
      return acc;
    }, {});
    var _data$state$placement = data[state.placement],
        x = _data$state$placement.x,
        y = _data$state$placement.y;

    if (state.modifiersData.popperOffsets != null) {
      state.modifiersData.popperOffsets.x += x;
      state.modifiersData.popperOffsets.y += y;
    }

    state.modifiersData[name] = data;
  } // eslint-disable-next-line import/no-unused-modules


  var offset$1 = {
    name: 'offset',
    enabled: true,
    phase: 'main',
    requires: ['popperOffsets'],
    fn: offset
  };

  var hash$1 = {
    left: 'right',
    right: 'left',
    bottom: 'top',
    top: 'bottom'
  };
  function getOppositePlacement(placement) {
    return placement.replace(/left|right|bottom|top/g, function (matched) {
      return hash$1[matched];
    });
  }

  var hash = {
    start: 'end',
    end: 'start'
  };
  function getOppositeVariationPlacement(placement) {
    return placement.replace(/start|end/g, function (matched) {
      return hash[matched];
    });
  }

  function computeAutoPlacement(state, options) {
    if (options === void 0) {
      options = {};
    }

    var _options = options,
        placement = _options.placement,
        boundary = _options.boundary,
        rootBoundary = _options.rootBoundary,
        padding = _options.padding,
        flipVariations = _options.flipVariations,
        _options$allowedAutoP = _options.allowedAutoPlacements,
        allowedAutoPlacements = _options$allowedAutoP === void 0 ? placements : _options$allowedAutoP;
    var variation = getVariation(placement);
    var placements$1 = variation ? flipVariations ? variationPlacements : variationPlacements.filter(function (placement) {
      return getVariation(placement) === variation;
    }) : basePlacements;
    var allowedPlacements = placements$1.filter(function (placement) {
      return allowedAutoPlacements.indexOf(placement) >= 0;
    });

    if (allowedPlacements.length === 0) {
      allowedPlacements = placements$1;

      {
        console.error(['Popper: The `allowedAutoPlacements` option did not allow any', 'placements. Ensure the `placement` option matches the variation', 'of the allowed placements.', 'For example, "auto" cannot be used to allow "bottom-start".', 'Use "auto-start" instead.'].join(' '));
      }
    } // $FlowFixMe[incompatible-type]: Flow seems to have problems with two array unions...


    var overflows = allowedPlacements.reduce(function (acc, placement) {
      acc[placement] = detectOverflow(state, {
        placement: placement,
        boundary: boundary,
        rootBoundary: rootBoundary,
        padding: padding
      })[getBasePlacement(placement)];
      return acc;
    }, {});
    return Object.keys(overflows).sort(function (a, b) {
      return overflows[a] - overflows[b];
    });
  }

  function getExpandedFallbackPlacements(placement) {
    if (getBasePlacement(placement) === auto) {
      return [];
    }

    var oppositePlacement = getOppositePlacement(placement);
    return [getOppositeVariationPlacement(placement), oppositePlacement, getOppositeVariationPlacement(oppositePlacement)];
  }

  function flip(_ref) {
    var state = _ref.state,
        options = _ref.options,
        name = _ref.name;

    if (state.modifiersData[name]._skip) {
      return;
    }

    var _options$mainAxis = options.mainAxis,
        checkMainAxis = _options$mainAxis === void 0 ? true : _options$mainAxis,
        _options$altAxis = options.altAxis,
        checkAltAxis = _options$altAxis === void 0 ? true : _options$altAxis,
        specifiedFallbackPlacements = options.fallbackPlacements,
        padding = options.padding,
        boundary = options.boundary,
        rootBoundary = options.rootBoundary,
        altBoundary = options.altBoundary,
        _options$flipVariatio = options.flipVariations,
        flipVariations = _options$flipVariatio === void 0 ? true : _options$flipVariatio,
        allowedAutoPlacements = options.allowedAutoPlacements;
    var preferredPlacement = state.options.placement;
    var basePlacement = getBasePlacement(preferredPlacement);
    var isBasePlacement = basePlacement === preferredPlacement;
    var fallbackPlacements = specifiedFallbackPlacements || (isBasePlacement || !flipVariations ? [getOppositePlacement(preferredPlacement)] : getExpandedFallbackPlacements(preferredPlacement));
    var placements = [preferredPlacement].concat(fallbackPlacements).reduce(function (acc, placement) {
      return acc.concat(getBasePlacement(placement) === auto ? computeAutoPlacement(state, {
        placement: placement,
        boundary: boundary,
        rootBoundary: rootBoundary,
        padding: padding,
        flipVariations: flipVariations,
        allowedAutoPlacements: allowedAutoPlacements
      }) : placement);
    }, []);
    var referenceRect = state.rects.reference;
    var popperRect = state.rects.popper;
    var checksMap = new Map();
    var makeFallbackChecks = true;
    var firstFittingPlacement = placements[0];

    for (var i = 0; i < placements.length; i++) {
      var placement = placements[i];

      var _basePlacement = getBasePlacement(placement);

      var isStartVariation = getVariation(placement) === start;
      var isVertical = [top, bottom].indexOf(_basePlacement) >= 0;
      var len = isVertical ? 'width' : 'height';
      var overflow = detectOverflow(state, {
        placement: placement,
        boundary: boundary,
        rootBoundary: rootBoundary,
        altBoundary: altBoundary,
        padding: padding
      });
      var mainVariationSide = isVertical ? isStartVariation ? right : left : isStartVariation ? bottom : top;

      if (referenceRect[len] > popperRect[len]) {
        mainVariationSide = getOppositePlacement(mainVariationSide);
      }

      var altVariationSide = getOppositePlacement(mainVariationSide);
      var checks = [];

      if (checkMainAxis) {
        checks.push(overflow[_basePlacement] <= 0);
      }

      if (checkAltAxis) {
        checks.push(overflow[mainVariationSide] <= 0, overflow[altVariationSide] <= 0);
      }

      if (checks.every(function (check) {
        return check;
      })) {
        firstFittingPlacement = placement;
        makeFallbackChecks = false;
        break;
      }

      checksMap.set(placement, checks);
    }

    if (makeFallbackChecks) {
      // `2` may be desired in some cases – research later
      var numberOfChecks = flipVariations ? 3 : 1;

      var _loop = function _loop(_i) {
        var fittingPlacement = placements.find(function (placement) {
          var checks = checksMap.get(placement);

          if (checks) {
            return checks.slice(0, _i).every(function (check) {
              return check;
            });
          }
        });

        if (fittingPlacement) {
          firstFittingPlacement = fittingPlacement;
          return "break";
        }
      };

      for (var _i = numberOfChecks; _i > 0; _i--) {
        var _ret = _loop(_i);

        if (_ret === "break") break;
      }
    }

    if (state.placement !== firstFittingPlacement) {
      state.modifiersData[name]._skip = true;
      state.placement = firstFittingPlacement;
      state.reset = true;
    }
  } // eslint-disable-next-line import/no-unused-modules


  var flip$1 = {
    name: 'flip',
    enabled: true,
    phase: 'main',
    fn: flip,
    requiresIfExists: ['offset'],
    data: {
      _skip: false
    }
  };

  function getAltAxis(axis) {
    return axis === 'x' ? 'y' : 'x';
  }

  function within(min$1, value, max$1) {
    return max(min$1, min(value, max$1));
  }
  function withinMaxClamp(min, value, max) {
    var v = within(min, value, max);
    return v > max ? max : v;
  }

  function preventOverflow(_ref) {
    var state = _ref.state,
        options = _ref.options,
        name = _ref.name;
    var _options$mainAxis = options.mainAxis,
        checkMainAxis = _options$mainAxis === void 0 ? true : _options$mainAxis,
        _options$altAxis = options.altAxis,
        checkAltAxis = _options$altAxis === void 0 ? false : _options$altAxis,
        boundary = options.boundary,
        rootBoundary = options.rootBoundary,
        altBoundary = options.altBoundary,
        padding = options.padding,
        _options$tether = options.tether,
        tether = _options$tether === void 0 ? true : _options$tether,
        _options$tetherOffset = options.tetherOffset,
        tetherOffset = _options$tetherOffset === void 0 ? 0 : _options$tetherOffset;
    var overflow = detectOverflow(state, {
      boundary: boundary,
      rootBoundary: rootBoundary,
      padding: padding,
      altBoundary: altBoundary
    });
    var basePlacement = getBasePlacement(state.placement);
    var variation = getVariation(state.placement);
    var isBasePlacement = !variation;
    var mainAxis = getMainAxisFromPlacement(basePlacement);
    var altAxis = getAltAxis(mainAxis);
    var popperOffsets = state.modifiersData.popperOffsets;
    var referenceRect = state.rects.reference;
    var popperRect = state.rects.popper;
    var tetherOffsetValue = typeof tetherOffset === 'function' ? tetherOffset(Object.assign({}, state.rects, {
      placement: state.placement
    })) : tetherOffset;
    var normalizedTetherOffsetValue = typeof tetherOffsetValue === 'number' ? {
      mainAxis: tetherOffsetValue,
      altAxis: tetherOffsetValue
    } : Object.assign({
      mainAxis: 0,
      altAxis: 0
    }, tetherOffsetValue);
    var offsetModifierState = state.modifiersData.offset ? state.modifiersData.offset[state.placement] : null;
    var data = {
      x: 0,
      y: 0
    };

    if (!popperOffsets) {
      return;
    }

    if (checkMainAxis) {
      var _offsetModifierState$;

      var mainSide = mainAxis === 'y' ? top : left;
      var altSide = mainAxis === 'y' ? bottom : right;
      var len = mainAxis === 'y' ? 'height' : 'width';
      var offset = popperOffsets[mainAxis];
      var min$1 = offset + overflow[mainSide];
      var max$1 = offset - overflow[altSide];
      var additive = tether ? -popperRect[len] / 2 : 0;
      var minLen = variation === start ? referenceRect[len] : popperRect[len];
      var maxLen = variation === start ? -popperRect[len] : -referenceRect[len]; // We need to include the arrow in the calculation so the arrow doesn't go
      // outside the reference bounds

      var arrowElement = state.elements.arrow;
      var arrowRect = tether && arrowElement ? getLayoutRect(arrowElement) : {
        width: 0,
        height: 0
      };
      var arrowPaddingObject = state.modifiersData['arrow#persistent'] ? state.modifiersData['arrow#persistent'].padding : getFreshSideObject();
      var arrowPaddingMin = arrowPaddingObject[mainSide];
      var arrowPaddingMax = arrowPaddingObject[altSide]; // If the reference length is smaller than the arrow length, we don't want
      // to include its full size in the calculation. If the reference is small
      // and near the edge of a boundary, the popper can overflow even if the
      // reference is not overflowing as well (e.g. virtual elements with no
      // width or height)

      var arrowLen = within(0, referenceRect[len], arrowRect[len]);
      var minOffset = isBasePlacement ? referenceRect[len] / 2 - additive - arrowLen - arrowPaddingMin - normalizedTetherOffsetValue.mainAxis : minLen - arrowLen - arrowPaddingMin - normalizedTetherOffsetValue.mainAxis;
      var maxOffset = isBasePlacement ? -referenceRect[len] / 2 + additive + arrowLen + arrowPaddingMax + normalizedTetherOffsetValue.mainAxis : maxLen + arrowLen + arrowPaddingMax + normalizedTetherOffsetValue.mainAxis;
      var arrowOffsetParent = state.elements.arrow && getOffsetParent(state.elements.arrow);
      var clientOffset = arrowOffsetParent ? mainAxis === 'y' ? arrowOffsetParent.clientTop || 0 : arrowOffsetParent.clientLeft || 0 : 0;
      var offsetModifierValue = (_offsetModifierState$ = offsetModifierState == null ? void 0 : offsetModifierState[mainAxis]) != null ? _offsetModifierState$ : 0;
      var tetherMin = offset + minOffset - offsetModifierValue - clientOffset;
      var tetherMax = offset + maxOffset - offsetModifierValue;
      var preventedOffset = within(tether ? min(min$1, tetherMin) : min$1, offset, tether ? max(max$1, tetherMax) : max$1);
      popperOffsets[mainAxis] = preventedOffset;
      data[mainAxis] = preventedOffset - offset;
    }

    if (checkAltAxis) {
      var _offsetModifierState$2;

      var _mainSide = mainAxis === 'x' ? top : left;

      var _altSide = mainAxis === 'x' ? bottom : right;

      var _offset = popperOffsets[altAxis];

      var _len = altAxis === 'y' ? 'height' : 'width';

      var _min = _offset + overflow[_mainSide];

      var _max = _offset - overflow[_altSide];

      var isOriginSide = [top, left].indexOf(basePlacement) !== -1;

      var _offsetModifierValue = (_offsetModifierState$2 = offsetModifierState == null ? void 0 : offsetModifierState[altAxis]) != null ? _offsetModifierState$2 : 0;

      var _tetherMin = isOriginSide ? _min : _offset - referenceRect[_len] - popperRect[_len] - _offsetModifierValue + normalizedTetherOffsetValue.altAxis;

      var _tetherMax = isOriginSide ? _offset + referenceRect[_len] + popperRect[_len] - _offsetModifierValue - normalizedTetherOffsetValue.altAxis : _max;

      var _preventedOffset = tether && isOriginSide ? withinMaxClamp(_tetherMin, _offset, _tetherMax) : within(tether ? _tetherMin : _min, _offset, tether ? _tetherMax : _max);

      popperOffsets[altAxis] = _preventedOffset;
      data[altAxis] = _preventedOffset - _offset;
    }

    state.modifiersData[name] = data;
  } // eslint-disable-next-line import/no-unused-modules


  var preventOverflow$1 = {
    name: 'preventOverflow',
    enabled: true,
    phase: 'main',
    fn: preventOverflow,
    requiresIfExists: ['offset']
  };

  var toPaddingObject = function toPaddingObject(padding, state) {
    padding = typeof padding === 'function' ? padding(Object.assign({}, state.rects, {
      placement: state.placement
    })) : padding;
    return mergePaddingObject(typeof padding !== 'number' ? padding : expandToHashMap(padding, basePlacements));
  };

  function arrow(_ref) {
    var _state$modifiersData$;

    var state = _ref.state,
        name = _ref.name,
        options = _ref.options;
    var arrowElement = state.elements.arrow;
    var popperOffsets = state.modifiersData.popperOffsets;
    var basePlacement = getBasePlacement(state.placement);
    var axis = getMainAxisFromPlacement(basePlacement);
    var isVertical = [left, right].indexOf(basePlacement) >= 0;
    var len = isVertical ? 'height' : 'width';

    if (!arrowElement || !popperOffsets) {
      return;
    }

    var paddingObject = toPaddingObject(options.padding, state);
    var arrowRect = getLayoutRect(arrowElement);
    var minProp = axis === 'y' ? top : left;
    var maxProp = axis === 'y' ? bottom : right;
    var endDiff = state.rects.reference[len] + state.rects.reference[axis] - popperOffsets[axis] - state.rects.popper[len];
    var startDiff = popperOffsets[axis] - state.rects.reference[axis];
    var arrowOffsetParent = getOffsetParent(arrowElement);
    var clientSize = arrowOffsetParent ? axis === 'y' ? arrowOffsetParent.clientHeight || 0 : arrowOffsetParent.clientWidth || 0 : 0;
    var centerToReference = endDiff / 2 - startDiff / 2; // Make sure the arrow doesn't overflow the popper if the center point is
    // outside of the popper bounds

    var min = paddingObject[minProp];
    var max = clientSize - arrowRect[len] - paddingObject[maxProp];
    var center = clientSize / 2 - arrowRect[len] / 2 + centerToReference;
    var offset = within(min, center, max); // Prevents breaking syntax highlighting...

    var axisProp = axis;
    state.modifiersData[name] = (_state$modifiersData$ = {}, _state$modifiersData$[axisProp] = offset, _state$modifiersData$.centerOffset = offset - center, _state$modifiersData$);
  }

  function effect(_ref2) {
    var state = _ref2.state,
        options = _ref2.options;
    var _options$element = options.element,
        arrowElement = _options$element === void 0 ? '[data-popper-arrow]' : _options$element;

    if (arrowElement == null) {
      return;
    } // CSS selector


    if (typeof arrowElement === 'string') {
      arrowElement = state.elements.popper.querySelector(arrowElement);

      if (!arrowElement) {
        return;
      }
    }

    {
      if (!isHTMLElement(arrowElement)) {
        console.error(['Popper: "arrow" element must be an HTMLElement (not an SVGElement).', 'To use an SVG arrow, wrap it in an HTMLElement that will be used as', 'the arrow.'].join(' '));
      }
    }

    if (!contains(state.elements.popper, arrowElement)) {
      {
        console.error(['Popper: "arrow" modifier\'s `element` must be a child of the popper', 'element.'].join(' '));
      }

      return;
    }

    state.elements.arrow = arrowElement;
  } // eslint-disable-next-line import/no-unused-modules


  var arrow$1 = {
    name: 'arrow',
    enabled: true,
    phase: 'main',
    fn: arrow,
    effect: effect,
    requires: ['popperOffsets'],
    requiresIfExists: ['preventOverflow']
  };

  function getSideOffsets(overflow, rect, preventedOffsets) {
    if (preventedOffsets === void 0) {
      preventedOffsets = {
        x: 0,
        y: 0
      };
    }

    return {
      top: overflow.top - rect.height - preventedOffsets.y,
      right: overflow.right - rect.width + preventedOffsets.x,
      bottom: overflow.bottom - rect.height + preventedOffsets.y,
      left: overflow.left - rect.width - preventedOffsets.x
    };
  }

  function isAnySideFullyClipped(overflow) {
    return [top, right, bottom, left].some(function (side) {
      return overflow[side] >= 0;
    });
  }

  function hide(_ref) {
    var state = _ref.state,
        name = _ref.name;
    var referenceRect = state.rects.reference;
    var popperRect = state.rects.popper;
    var preventedOffsets = state.modifiersData.preventOverflow;
    var referenceOverflow = detectOverflow(state, {
      elementContext: 'reference'
    });
    var popperAltOverflow = detectOverflow(state, {
      altBoundary: true
    });
    var referenceClippingOffsets = getSideOffsets(referenceOverflow, referenceRect);
    var popperEscapeOffsets = getSideOffsets(popperAltOverflow, popperRect, preventedOffsets);
    var isReferenceHidden = isAnySideFullyClipped(referenceClippingOffsets);
    var hasPopperEscaped = isAnySideFullyClipped(popperEscapeOffsets);
    state.modifiersData[name] = {
      referenceClippingOffsets: referenceClippingOffsets,
      popperEscapeOffsets: popperEscapeOffsets,
      isReferenceHidden: isReferenceHidden,
      hasPopperEscaped: hasPopperEscaped
    };
    state.attributes.popper = Object.assign({}, state.attributes.popper, {
      'data-popper-reference-hidden': isReferenceHidden,
      'data-popper-escaped': hasPopperEscaped
    });
  } // eslint-disable-next-line import/no-unused-modules


  var hide$1 = {
    name: 'hide',
    enabled: true,
    phase: 'main',
    requiresIfExists: ['preventOverflow'],
    fn: hide
  };

  var defaultModifiers$1 = [eventListeners, popperOffsets$1, computeStyles$1, applyStyles$1];
  var createPopper$1 = /*#__PURE__*/popperGenerator({
    defaultModifiers: defaultModifiers$1
  }); // eslint-disable-next-line import/no-unused-modules

  var defaultModifiers = [eventListeners, popperOffsets$1, computeStyles$1, applyStyles$1, offset$1, flip$1, preventOverflow$1, arrow$1, hide$1];
  var createPopper = /*#__PURE__*/popperGenerator({
    defaultModifiers: defaultModifiers
  }); // eslint-disable-next-line import/no-unused-modules

  exports.applyStyles = applyStyles$1;
  exports.arrow = arrow$1;
  exports.computeStyles = computeStyles$1;
  exports.createPopper = createPopper;
  exports.createPopperLite = createPopper$1;
  exports.defaultModifiers = defaultModifiers;
  exports.detectOverflow = detectOverflow;
  exports.eventListeners = eventListeners;
  exports.flip = flip$1;
  exports.hide = hide$1;
  exports.offset = offset$1;
  exports.popperGenerator = popperGenerator;
  exports.popperOffsets = popperOffsets$1;
  exports.preventOverflow = preventOverflow$1;

  Object.defineProperty(exports, '__esModule', { value: true });

})));
//# sourceMappingURL=popper.js.map

/*!
  * Tempus Dominus v6.9.4 (https://getdatepicker.com/)
  * Copyright 2013-2024 Jonathan Peterson
  * Licensed under MIT (https://github.com/Eonasdan/tempus-dominus/blob/master/LICENSE)
  */
(function (global, factory) {
  typeof exports === 'object' && typeof module !== 'undefined' ? factory(exports) :
  typeof define === 'function' && define.amd ? define(['exports'], factory) :
  (global = typeof globalThis !== 'undefined' ? globalThis : global || self, factory(global.tempusDominus = {}));
})(this, (function (exports) { 'use strict';

  class TdError extends Error {
  }
  class ErrorMessages {
      constructor() {
          this.base = 'TD:';
          //#endregion
          //#region used with notify.error
          /**
           * Used with an Error Event type if the user selects a date that
           * fails restriction validation.
           */
          this.failedToSetInvalidDate = 'Failed to set invalid date';
          /**
           * Used with an Error Event type when a user changes the value of the
           * input field directly, and does not provide a valid date.
           */
          this.failedToParseInput = 'Failed parse input field';
          //#endregion
      }
      //#region out to console
      /**
       * Throws an error indicating that a key in the options object is invalid.
       * @param optionName
       */
      unexpectedOption(optionName) {
          const error = new TdError(`${this.base} Unexpected option: ${optionName} does not match a known option.`);
          error.code = 1;
          throw error;
      }
      /**
       * Throws an error indicating that one more keys in the options object is invalid.
       * @param optionName
       */
      unexpectedOptions(optionName) {
          const error = new TdError(`${this.base}: ${optionName.join(', ')}`);
          error.code = 1;
          throw error;
      }
      /**
       * Throws an error when an option is provide an unsupported value.
       * For example a value of 'cheese' for toolbarPlacement which only supports
       * 'top', 'bottom', 'default'.
       * @param optionName
       * @param badValue
       * @param validOptions
       */
      unexpectedOptionValue(optionName, badValue, validOptions) {
          const error = new TdError(`${this.base} Unexpected option value: ${optionName} does not accept a value of "${badValue}". Valid values are: ${validOptions.join(', ')}`);
          error.code = 2;
          throw error;
      }
      /**
       * Throws an error when an option value is the wrong type.
       * For example a string value was provided to multipleDates which only
       * supports true or false.
       * @param optionName
       * @param badType
       * @param expectedType
       */
      typeMismatch(optionName, badType, expectedType) {
          const error = new TdError(`${this.base} Mismatch types: ${optionName} has a type of ${badType} instead of the required ${expectedType}`);
          error.code = 3;
          throw error;
      }
      /**
       * Throws an error when an option value is  outside of the expected range.
       * For example restrictions.daysOfWeekDisabled excepts a value between 0 and 6.
       * @param optionName
       * @param lower
       * @param upper
       */
      numbersOutOfRange(optionName, lower, upper) {
          const error = new TdError(`${this.base} ${optionName} expected an array of number between ${lower} and ${upper}.`);
          error.code = 4;
          throw error;
      }
      /**
       * Throws an error when a value for a date options couldn't be parsed. Either
       * the option was an invalid string or an invalid Date object.
       * @param optionName
       * @param date
       * @param soft If true, logs a warning instead of an error.
       */
      //eslint-disable-next-line @typescript-eslint/no-explicit-any
      failedToParseDate(optionName, date, soft = false) {
          const error = new TdError(`${this.base} Could not correctly parse "${date}" to a date for ${optionName}.`);
          error.code = 5;
          if (!soft)
              throw error;
          console.warn(error);
      }
      /**
       * Throws when an element to attach to was not provided in the constructor.
       */
      mustProvideElement() {
          const error = new TdError(`${this.base} No element was provided.`);
          error.code = 6;
          throw error;
      }
      /**
       * Throws if providing an array for the events to subscribe method doesn't have
       * the same number of callbacks. E.g., subscribe([1,2], [1])
       */
      subscribeMismatch() {
          const error = new TdError(`${this.base} The subscribed events does not match the number of callbacks`);
          error.code = 7;
          throw error;
      }
      /**
       * Throws if the configuration has conflicting rules e.g. minDate is after maxDate
       */
      conflictingConfiguration(message) {
          const error = new TdError(`${this.base} A configuration value conflicts with another rule. ${message}`);
          error.code = 8;
          throw error;
      }
      /**
       * customDateFormat errors
       */
      customDateFormatError(message) {
          const error = new TdError(`${this.base} Custom Date Format: ${message}`);
          error.code = 9;
          throw error;
      }
      /**
       * Logs a warning if a date option value is provided as a string, instead of
       * a date/datetime object.
       */
      dateString() {
          console.warn(`${this.base} Using a string for date options is not recommended unless you specify an ISO string or use the customDateFormat plugin.`);
      }
      deprecatedWarning(message, remediation) {
          console.warn(`${this.base} Warning ${message} is deprecated and will be removed in a future version. ${remediation}`);
      }
      throwError(message) {
          const error = new TdError(`${this.base} ${message}`);
          error.code = 9;
          throw error;
      }
  }

  // this is not the way I want this to stay but nested classes seemed to blown up once its compiled.
  const NAME = 'tempus-dominus', dataKey = 'td';
  /**
   * Events
   */
  class Events {
      constructor() {
          this.key = `.${dataKey}`;
          /**
           * Change event. Fired when the user selects a date.
           * See also EventTypes.ChangeEvent
           */
          this.change = `change${this.key}`;
          /**
           * Emit when the view changes for example from month view to the year view.
           * See also EventTypes.ViewUpdateEvent
           */
          this.update = `update${this.key}`;
          /**
           * Emits when a selected date or value from the input field fails to meet the provided validation rules.
           * See also EventTypes.FailEvent
           */
          this.error = `error${this.key}`;
          /**
           * Show event
           * @event Events#show
           */
          this.show = `show${this.key}`;
          /**
           * Hide event
           * @event Events#hide
           */
          this.hide = `hide${this.key}`;
          // blur and focus are used in the jQuery provider but are otherwise unused.
          // keyup/down will be used later for keybinding options
          this.blur = `blur${this.key}`;
          this.focus = `focus${this.key}`;
          this.keyup = `keyup${this.key}`;
          this.keydown = `keydown${this.key}`;
      }
  }
  class Css {
      constructor() {
          /**
           * The outer element for the widget.
           */
          this.widget = `${NAME}-widget`;
          /**
           * Hold the previous, next and switcher divs
           */
          this.calendarHeader = 'calendar-header';
          /**
           * The element for the action to change the calendar view. E.g. month -> year.
           */
          this.switch = 'picker-switch';
          /**
           * The elements for all the toolbar options
           */
          this.toolbar = 'toolbar';
          /**
           * Disables the hover and rounding affect.
           */
          this.noHighlight = 'no-highlight';
          /**
           * Applied to the widget element when the side by side option is in use.
           */
          this.sideBySide = 'timepicker-sbs';
          /**
           * The element for the action to change the calendar view, e.g. August -> July
           */
          this.previous = 'previous';
          /**
           * The element for the action to change the calendar view, e.g. August -> September
           */
          this.next = 'next';
          /**
           * Applied to any action that would violate any restriction options. ALso applied
           * to an input field if the disabled function is called.
           */
          this.disabled = 'disabled';
          /**
           * Applied to any date that is less than requested view,
           * e.g. the last day of the previous month.
           */
          this.old = 'old';
          /**
           * Applied to any date that is greater than of requested view,
           * e.g. the last day of the previous month.
           */
          this.new = 'new';
          /**
           * Applied to any date that is currently selected.
           */
          this.active = 'active';
          //#region date element
          /**
           * The outer element for the calendar view.
           */
          this.dateContainer = 'date-container';
          /**
           * The outer element for the decades view.
           */
          this.decadesContainer = `${this.dateContainer}-decades`;
          /**
           * Applied to elements within the decade container, e.g. 2020, 2030
           */
          this.decade = 'decade';
          /**
           * The outer element for the years view.
           */
          this.yearsContainer = `${this.dateContainer}-years`;
          /**
           * Applied to elements within the years container, e.g. 2021, 2021
           */
          this.year = 'year';
          /**
           * The outer element for the month view.
           */
          this.monthsContainer = `${this.dateContainer}-months`;
          /**
           * Applied to elements within the month container, e.g. January, February
           */
          this.month = 'month';
          /**
           * The outer element for the calendar view.
           */
          this.daysContainer = `${this.dateContainer}-days`;
          /**
           * Applied to elements within the day container, e.g. 1, 2..31
           */
          this.day = 'day';
          /**
           * If display.calendarWeeks is enabled, a column displaying the week of year
           * is shown. This class is applied to each cell in that column.
           */
          this.calendarWeeks = 'cw';
          /**
           * Applied to the first row of the calendar view, e.g. Sunday, Monday
           */
          this.dayOfTheWeek = 'dow';
          /**
           * Applied to the current date on the calendar view.
           */
          this.today = 'today';
          /**
           * Applied to the locale's weekend dates on the calendar view, e.g. Sunday, Saturday
           */
          this.weekend = 'weekend';
          this.rangeIn = 'range-in';
          this.rangeStart = 'range-start';
          this.rangeEnd = 'range-end';
          //#endregion
          //#region time element
          /**
           * The outer element for all time related elements.
           */
          this.timeContainer = 'time-container';
          /**
           * Applied the separator columns between time elements, e.g. hour *:* minute *:* second
           */
          this.separator = 'separator';
          /**
           * The outer element for the clock view.
           */
          this.clockContainer = `${this.timeContainer}-clock`;
          /**
           * The outer element for the hours selection view.
           */
          this.hourContainer = `${this.timeContainer}-hour`;
          /**
           * The outer element for the minutes selection view.
           */
          this.minuteContainer = `${this.timeContainer}-minute`;
          /**
           * The outer element for the seconds selection view.
           */
          this.secondContainer = `${this.timeContainer}-second`;
          /**
           * Applied to each element in the hours selection view.
           */
          this.hour = 'hour';
          /**
           * Applied to each element in the minutes selection view.
           */
          this.minute = 'minute';
          /**
           * Applied to each element in the seconds selection view.
           */
          this.second = 'second';
          /**
           * Applied AM/PM toggle button.
           */
          this.toggleMeridiem = 'toggleMeridiem';
          //#endregion
          //#region collapse
          /**
           * Applied the element of the current view mode, e.g. calendar or clock.
           */
          this.show = 'show';
          /**
           * Applied to the currently showing view mode during a transition
           * between calendar and clock views
           */
          this.collapsing = 'td-collapsing';
          /**
           * Applied to the currently hidden view mode.
           */
          this.collapse = 'td-collapse';
          //#endregion
          /**
           * Applied to the widget when the option display.inline is enabled.
           */
          this.inline = 'inline';
          /**
           * Applied to the widget when the option display.theme is light.
           */
          this.lightTheme = 'light';
          /**
           * Applied to the widget when the option display.theme is dark.
           */
          this.darkTheme = 'dark';
          /**
           * Used for detecting if the system color preference is dark mode
           */
          this.isDarkPreferredQuery = '(prefers-color-scheme: dark)';
      }
  }
  class Namespace {
  }
  Namespace.NAME = NAME;
  // noinspection JSUnusedGlobalSymbols
  Namespace.dataKey = dataKey;
  Namespace.events = new Events();
  Namespace.css = new Css();
  Namespace.errorMessages = new ErrorMessages();

  const DefaultFormatLocalization = {
      dateFormats: {
          LTS: 'h:mm:ss T',
          LT: 'h:mm T',
          L: 'MM/dd/yyyy',
          LL: 'MMMM d, yyyy',
          LLL: 'MMMM d, yyyy h:mm T',
          LLLL: 'dddd, MMMM d, yyyy h:mm T',
      },
      format: 'L LT',
      locale: 'default',
      hourCycle: undefined,
      ordinal: (n) => {
          const s = ['th', 'st', 'nd', 'rd'];
          const v = n % 100;
          return `[${n}${s[(v - 20) % 10] || s[v] || s[0]}]`;
      },
  };
  var DefaultFormatLocalization$1 = { ...DefaultFormatLocalization };

  exports.Unit = void 0;
  (function (Unit) {
      Unit["seconds"] = "seconds";
      Unit["minutes"] = "minutes";
      Unit["hours"] = "hours";
      Unit["date"] = "date";
      Unit["month"] = "month";
      Unit["year"] = "year";
  })(exports.Unit || (exports.Unit = {}));
  const twoDigitTemplate = {
      month: '2-digit',
      day: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit',
  };
  /**
   * Returns an Intl format object based on the provided object
   * @param unit
   */
  const getFormatByUnit = (unit) => {
      switch (unit) {
          case 'date':
              return { dateStyle: 'short' };
          case 'month':
              return {
                  month: 'numeric',
                  year: 'numeric',
              };
          case 'year':
              return { year: 'numeric' };
      }
  };
  /**
   * Attempts to guess the hour cycle of the given local
   * @param locale
   */
  const guessHourCycle = (locale) => {
      if (!locale)
          return 'h12';
      // noinspection SpellCheckingInspection
      const template = {
          hour: '2-digit',
          minute: '2-digit',
          numberingSystem: 'latn',
      };
      const dt = new DateTime().setLocalization({ locale });
      dt.hours = 0;
      const start = dt.parts(undefined, template).hour;
      //midnight is 12 so en-US style 12 AM
      if (start === '12')
          return 'h12';
      //midnight is 24 is from 00-24
      if (start === '24')
          return 'h24';
      dt.hours = 23;
      const end = dt.parts(undefined, template).hour;
      //if midnight is 00 and hour 23 is 11 then
      if (start === '00' && end === '11')
          return 'h11';
      if (start === '00' && end === '23')
          return 'h23';
      console.warn(`couldn't determine hour cycle for ${locale}. start: ${start}. end: ${end}`);
      return undefined;
  };
  /**
   * For the most part this object behaves exactly the same way
   * as the native Date object with a little extra spice.
   */
  class DateTime extends Date {
      constructor() {
          super(...arguments);
          this.localization = DefaultFormatLocalization$1;
          this.nonLeapLadder = [
              0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334,
          ];
          this.leapLadder = [0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335];
          //#region CDF stuff
          this.dateTimeRegex = 
          //is regex cannot be simplified beyond what it already is
          /(\[[^[\]]*])|y{1,4}|M{1,4}|d{1,4}|H{1,2}|h{1,2}|t|T|m{1,2}|s{1,2}|f{3}/g; //NOSONAR
          this.formattingTokens = /(\[[^[\]]*])|([-_:/.,()\s]+)|(T|t|yyyy|yy?|MM?M?M?|Do|dd?d?d?|hh?|HH?|mm?|ss?)/g; //NOSONAR is regex cannot be simplified beyond what it already is
          this.match2 = /\d\d/; // 00 - 99
          this.match3 = /\d{3}/; // 000 - 999
          this.match4 = /\d{4}/; // 0000 - 9999
          this.match1to2 = /\d\d?/; // 0 - 99
          this.matchSigned = /[+-]?\d+/; // -inf - inf
          this.matchOffset = /[+-]\d\d:?(\d\d)?|Z/; // +00:00 -00:00 +0000 or -0000 +00 or Z
          this.matchWord = /[^\d_:/,\-()\s]+/; // Word
          this.zoneExpressions = [
              this.matchOffset,
              (obj, input) => {
                  obj.offset = this.offsetFromString(input);
              },
          ];
          this.expressions = {
              t: {
                  pattern: undefined,
                  parser: (obj, input) => {
                      obj.afternoon = this.meridiemMatch(input);
                  },
              },
              T: {
                  pattern: undefined,
                  parser: (obj, input) => {
                      obj.afternoon = this.meridiemMatch(input);
                  },
              },
              fff: {
                  pattern: this.match3,
                  parser: (obj, input) => {
                      obj.milliseconds = +input;
                  },
              },
              s: {
                  pattern: this.match1to2,
                  parser: this.addInput('seconds'),
              },
              ss: {
                  pattern: this.match1to2,
                  parser: this.addInput('seconds'),
              },
              m: {
                  pattern: this.match1to2,
                  parser: this.addInput('minutes'),
              },
              mm: {
                  pattern: this.match1to2,
                  parser: this.addInput('minutes'),
              },
              H: {
                  pattern: this.match1to2,
                  parser: this.addInput('hours'),
              },
              h: {
                  pattern: this.match1to2,
                  parser: this.addInput('hours'),
              },
              HH: {
                  pattern: this.match1to2,
                  parser: this.addInput('hours'),
              },
              hh: {
                  pattern: this.match1to2,
                  parser: this.addInput('hours'),
              },
              d: {
                  pattern: this.match1to2,
                  parser: this.addInput('day'),
              },
              dd: {
                  pattern: this.match2,
                  parser: this.addInput('day'),
              },
              Do: {
                  pattern: this.matchWord,
                  parser: (obj, input) => {
                      obj.day = +(input.match(/\d+/)[0] || 1);
                      if (!this.localization.ordinal)
                          return;
                      for (let i = 1; i <= 31; i += 1) {
                          if (this.localization.ordinal(i).replace(/[[\]]/g, '') === input) {
                              obj.day = i;
                          }
                      }
                  },
              },
              M: {
                  pattern: this.match1to2,
                  parser: this.addInput('month'),
              },
              MM: {
                  pattern: this.match2,
                  parser: this.addInput('month'),
              },
              MMM: {
                  pattern: this.matchWord,
                  parser: (obj, input) => {
                      const months = this.getAllMonths();
                      const monthsShort = this.getAllMonths('short');
                      const matchIndex = (monthsShort || months.map((_) => _.slice(0, 3))).indexOf(input) + 1;
                      if (matchIndex < 1) {
                          throw new Error();
                      }
                      obj.month = matchIndex % 12 || matchIndex;
                  },
              },
              MMMM: {
                  pattern: this.matchWord,
                  parser: (obj, input) => {
                      const months = this.getAllMonths();
                      const matchIndex = months.indexOf(input) + 1;
                      if (matchIndex < 1) {
                          throw new Error();
                      }
                      obj.month = matchIndex % 12 || matchIndex;
                  },
              },
              y: {
                  pattern: this.matchSigned,
                  parser: this.addInput('year'),
              },
              yy: {
                  pattern: this.match2,
                  parser: (obj, input) => {
                      obj.year = this.parseTwoDigitYear(+input);
                  },
              },
              yyyy: {
                  pattern: this.match4,
                  parser: this.addInput('year'),
              },
              // z: this.zoneExpressions,
              // zz: this.zoneExpressions,
              // zzz: this.zoneExpressions
          };
          //#endregion CDF stuff
      }
      /**
       * Chainable way to set the {@link locale}
       * @param value
       * @deprecated use setLocalization with a FormatLocalization object instead
       */
      setLocale(value) {
          if (!this.localization) {
              this.localization = DefaultFormatLocalization$1;
              this.localization.locale = value;
          }
          return this;
      }
      /**
       * Chainable way to set the {@link localization}
       * @param value
       */
      setLocalization(value) {
          this.localization = value;
          return this;
      }
      /**
       * Converts a plain JS date object to a DateTime object.
       * Doing this allows access to format, etc.
       * @param  date
       * @param locale this parameter is deprecated. Use formatLocalization instead.
       * @param formatLocalization
       */
      static convert(date, locale = 'default', formatLocalization = undefined) {
          if (!date)
              throw new Error(`A date is required`);
          if (!formatLocalization) {
              formatLocalization = DefaultFormatLocalization$1;
              formatLocalization.locale = locale;
          }
          return new DateTime(date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds()).setLocalization(formatLocalization);
      }
      /**
       * Native date manipulations are not pure functions. This function creates a duplicate of the DateTime object.
       */
      get clone() {
          return new DateTime(this.year, this.month, this.date, this.hours, this.minutes, this.seconds, this.getMilliseconds()).setLocalization(this.localization);
      }
      static isValid(d) {
          if (d === undefined || JSON.stringify(d) === 'null')
              return false;
          if (d.constructor.name === DateTime.name)
              return true;
          return false;
      }
      /**
       * Sets the current date to the start of the {@link unit} provided
       * Example: Consider a date of "April 30, 2021, 11:45:32.984 AM" => new DateTime(2021, 3, 30, 11, 45, 32, 984).startOf('month')
       * would return April 1, 2021, 12:00:00.000 AM (midnight)
       * @param unit
       * @param startOfTheWeek Allows for the changing the start of the week.
       */
      startOf(unit, startOfTheWeek = 0) {
          if (this[unit] === undefined)
              throw new Error(`Unit '${unit}' is not valid`);
          switch (unit) {
              case 'seconds':
                  this.setMilliseconds(0);
                  break;
              case 'minutes':
                  this.setSeconds(0, 0);
                  break;
              case 'hours':
                  this.setMinutes(0, 0, 0);
                  break;
              case 'date':
                  this.setHours(0, 0, 0, 0);
                  break;
              case 'weekDay': {
                  this.startOf(exports.Unit.date);
                  if (this.weekDay === startOfTheWeek)
                      break;
                  const goBack = (this.weekDay - startOfTheWeek + 7) % 7;
                  this.manipulate(goBack * -1, exports.Unit.date);
                  break;
              }
              case 'month':
                  this.startOf(exports.Unit.date);
                  this.setDate(1);
                  break;
              case 'year':
                  this.startOf(exports.Unit.date);
                  this.setMonth(0, 1);
                  break;
          }
          return this;
      }
      /**
       * Sets the current date to the end of the {@link unit} provided
       * Example: Consider a date of "April 30, 2021, 11:45:32.984 AM" => new DateTime(2021, 3, 30, 11, 45, 32, 984).endOf('month')
       * would return April 30, 2021, 11:59:59.999 PM
       * @param unit
       * @param startOfTheWeek
       */
      endOf(unit, startOfTheWeek = 0) {
          if (this[unit] === undefined)
              throw new Error(`Unit '${unit}' is not valid`);
          switch (unit) {
              case 'seconds':
                  this.setMilliseconds(999);
                  break;
              case 'minutes':
                  this.setSeconds(59, 999);
                  break;
              case 'hours':
                  this.setMinutes(59, 59, 999);
                  break;
              case 'date':
                  this.setHours(23, 59, 59, 999);
                  break;
              case 'weekDay': {
                  this.endOf(exports.Unit.date);
                  const endOfWeek = 6 + startOfTheWeek;
                  if (this.weekDay === endOfWeek)
                      break;
                  this.manipulate(endOfWeek - this.weekDay, exports.Unit.date);
                  break;
              }
              case 'month':
                  this.endOf(exports.Unit.date);
                  this.manipulate(1, exports.Unit.month);
                  this.setDate(0);
                  break;
              case 'year':
                  this.endOf(exports.Unit.date);
                  this.setMonth(11, 31);
                  break;
          }
          return this;
      }
      /**
       * Change a {@link unit} value. Value can be positive or negative
       * Example: Consider a date of "April 30, 2021, 11:45:32.984 AM" => new DateTime(2021, 3, 30, 11, 45, 32, 984).manipulate(1, 'month')
       * would return May 30, 2021, 11:45:32.984 AM
       * @param value A positive or negative number
       * @param unit
       */
      manipulate(value, unit) {
          if (this[unit] === undefined)
              throw new Error(`Unit '${unit}' is not valid`);
          this[unit] += value;
          return this;
      }
      /**
       * Return true if {@link compare} is before this date
       * @param compare The Date/DateTime to compare
       * @param unit If provided, uses {@link startOf} for
       * comparison.
       */
      isBefore(compare, unit) {
          // If the comparisons is undefined, return false
          if (!DateTime.isValid(compare))
              return false;
          if (!unit)
              return this.valueOf() < compare.valueOf();
          if (this[unit] === undefined)
              throw new Error(`Unit '${unit}' is not valid`);
          return (this.clone.startOf(unit).valueOf() < compare.clone.startOf(unit).valueOf());
      }
      /**
       * Return true if {@link compare} is after this date
       * @param compare The Date/DateTime to compare
       * @param unit If provided, uses {@link startOf} for
       * comparison.
       */
      isAfter(compare, unit) {
          // If the comparisons is undefined, return false
          if (!DateTime.isValid(compare))
              return false;
          if (!unit)
              return this.valueOf() > compare.valueOf();
          if (this[unit] === undefined)
              throw new Error(`Unit '${unit}' is not valid`);
          return (this.clone.startOf(unit).valueOf() > compare.clone.startOf(unit).valueOf());
      }
      /**
       * Return true if {@link compare} is same this date
       * @param compare The Date/DateTime to compare
       * @param unit If provided, uses {@link startOf} for
       * comparison.
       */
      isSame(compare, unit) {
          // If the comparisons is undefined, return false
          if (!DateTime.isValid(compare))
              return false;
          if (!unit)
              return this.valueOf() === compare.valueOf();
          if (this[unit] === undefined)
              throw new Error(`Unit '${unit}' is not valid`);
          compare = DateTime.convert(compare);
          return (this.clone.startOf(unit).valueOf() === compare.startOf(unit).valueOf());
      }
      /**
       * Check if this is between two other DateTimes, optionally looking at unit scale. The match is exclusive.
       * @param left
       * @param right
       * @param unit.
       * @param inclusivity. A [ indicates inclusion of a value. A ( indicates exclusion.
       * If the inclusivity parameter is used, both indicators must be passed.
       */
      isBetween(left, right, unit, inclusivity = '()') {
          // If one of the comparisons is undefined, return false
          if (!DateTime.isValid(left) || !DateTime.isValid(right))
              return false;
          // If a unit is provided and is not a valid property of the DateTime object, throw an error
          if (unit && this[unit] === undefined) {
              throw new Error(`Unit '${unit}' is not valid`);
          }
          const leftInclusivity = inclusivity[0] === '(';
          const rightInclusivity = inclusivity[1] === ')';
          const isLeftInRange = leftInclusivity
              ? this.isAfter(left, unit)
              : !this.isBefore(left, unit);
          const isRightInRange = rightInclusivity
              ? this.isBefore(right, unit)
              : !this.isAfter(right, unit);
          return isLeftInRange && isRightInRange;
      }
      /**
       * Returns flattened object of the date. Does not include literals
       * @param locale
       * @param template
       */
      parts(locale = this.localization.locale, template = { dateStyle: 'full', timeStyle: 'long' }) {
          const parts = {};
          new Intl.DateTimeFormat(locale, template)
              .formatToParts(this)
              .filter((x) => x.type !== 'literal')
              .forEach((x) => (parts[x.type] = x.value));
          return parts;
      }
      /**
       * Shortcut to Date.getSeconds()
       */
      get seconds() {
          return this.getSeconds();
      }
      /**
       * Shortcut to Date.setSeconds()
       */
      set seconds(value) {
          this.setSeconds(value);
      }
      /**
       * Returns two digit hours
       */
      get secondsFormatted() {
          return this.parts(undefined, twoDigitTemplate).second;
      }
      /**
       * Shortcut to Date.getMinutes()
       */
      get minutes() {
          return this.getMinutes();
      }
      /**
       * Shortcut to Date.setMinutes()
       */
      set minutes(value) {
          this.setMinutes(value);
      }
      /**
       * Returns two digit minutes
       */
      get minutesFormatted() {
          return this.parts(undefined, twoDigitTemplate).minute;
      }
      /**
       * Shortcut to Date.getHours()
       */
      get hours() {
          return this.getHours();
      }
      /**
       * Shortcut to Date.setHours()
       */
      set hours(value) {
          this.setHours(value);
      }
      /**
       * Returns two digit hour, e.g. 01...10
       * @param hourCycle Providing an hour cycle will change 00 to 24 depending on the given value.
       */
      getHoursFormatted(hourCycle = 'h12') {
          return this.parts(undefined, { ...twoDigitTemplate, hourCycle: hourCycle })
              .hour;
      }
      /**
       * Get the meridiem of the date. E.g. AM or PM.
       * If the {@link locale} provides a "dayPeriod" then this will be returned,
       * otherwise it will return AM or PM.
       * @param locale
       */
      meridiem(locale = this.localization.locale) {
          return new Intl.DateTimeFormat(locale, {
              hour: 'numeric',
              hour12: true,
          })
              .formatToParts(this)
              .find((p) => p.type === 'dayPeriod')?.value;
      }
      /**
       * Shortcut to Date.getDate()
       */
      get date() {
          return this.getDate();
      }
      /**
       * Shortcut to Date.setDate()
       */
      set date(value) {
          this.setDate(value);
      }
      /**
       * Return two digit date
       */
      get dateFormatted() {
          return this.parts(undefined, twoDigitTemplate).day;
      }
      /**
       * Shortcut to Date.getDay()
       */
      get weekDay() {
          return this.getDay();
      }
      /**
       * Shortcut to Date.getMonth()
       */
      get month() {
          return this.getMonth();
      }
      /**
       * Shortcut to Date.setMonth()
       */
      set month(value) {
          const targetMonth = new Date(this.year, value + 1);
          targetMonth.setDate(0);
          const endOfMonth = targetMonth.getDate();
          if (this.date > endOfMonth) {
              this.date = endOfMonth;
          }
          this.setMonth(value);
      }
      /**
       * Return two digit, human expected month. E.g. January = 1, December = 12
       */
      get monthFormatted() {
          return this.parts(undefined, twoDigitTemplate).month;
      }
      /**
       * Shortcut to Date.getFullYear()
       */
      get year() {
          return this.getFullYear();
      }
      /**
       * Shortcut to Date.setFullYear()
       */
      set year(value) {
          this.setFullYear(value);
      }
      // borrowed a bunch of stuff from Luxon
      /**
       * Gets the week of the year
       */
      get week() {
          const ordinal = this.computeOrdinal(), weekday = this.getUTCDay();
          let weekNumber = Math.floor((ordinal - weekday + 10) / 7);
          if (weekNumber < 1) {
              weekNumber = this.weeksInWeekYear();
          }
          else if (weekNumber > this.weeksInWeekYear()) {
              weekNumber = 1;
          }
          return weekNumber;
      }
      /**
       * Returns the number of weeks in the year
       */
      weeksInWeekYear() {
          const p1 = (this.year +
              Math.floor(this.year / 4) -
              Math.floor(this.year / 100) +
              Math.floor(this.year / 400)) %
              7, last = this.year - 1, p2 = (last +
              Math.floor(last / 4) -
              Math.floor(last / 100) +
              Math.floor(last / 400)) %
              7;
          return p1 === 4 || p2 === 3 ? 53 : 52;
      }
      /**
       * Returns true or false depending on if the year is a leap year or not.
       */
      get isLeapYear() {
          return (this.year % 4 === 0 && (this.year % 100 !== 0 || this.year % 400 === 0));
      }
      computeOrdinal() {
          return (this.date +
              (this.isLeapYear ? this.leapLadder : this.nonLeapLadder)[this.month]);
      }
      /**
       * Returns a list of month values based on the current locale
       */
      getAllMonths(format = 'long') {
          const applyFormat = new Intl.DateTimeFormat(this.localization.locale, {
              month: format,
          }).format;
          return [...Array(12).keys()].map((m) => applyFormat(new Date(2021, m)));
      }
      /**
       * Replaces an expanded token set (e.g. LT/LTS)
       */
      replaceTokens(formatStr, formats) {
          /***
           * _ => match
           * a => first capture group. Anything between [ and ]
           * b => second capture group
           */
          return formatStr.replace(/(\[[^[\]]*])|(LTS?|l{1,4}|L{1,4})/g, (_, a, b) => {
              const B = b && b.toUpperCase();
              return a || formats[B] || DefaultFormatLocalization$1.dateFormats[B];
          });
      }
      parseTwoDigitYear(input) {
          return input + (input > 68 ? 1900 : 2000);
      }
      offsetFromString(input) {
          if (!input)
              return 0;
          if (input === 'Z')
              return 0;
          const [first, second, third] = input.match(/([+-]|\d\d)/g);
          const minutes = +second * 60 + (+third || 0);
          const signed = first === '+' ? -minutes : minutes;
          return minutes === 0 ? 0 : signed; // eslint-disable-line no-nested-ternary
      }
      /**
       * z = -4, zz = -04, zzz = -0400
       * @param date
       * @param style
       * @private
       */
      zoneInformation(date, style) {
          let name = date
              .parts(this.localization.locale, { timeZoneName: 'longOffset' })
              .timeZoneName.replace('GMT', '')
              .replace(':', '');
          const negative = name.includes('-');
          name = name.replace('-', '');
          if (style === 'z')
              name = name.substring(1, 2);
          else if (style === 'zz')
              name = name.substring(0, 2);
          return `${negative ? '-' : ''}${name}`;
      }
      addInput(property) {
          return (obj, input) => {
              obj[property] = +input;
          };
      }
      getLocaleAfternoon() {
          return new Intl.DateTimeFormat(this.localization.locale, {
              hour: 'numeric',
              hour12: true,
          })
              .formatToParts(new Date(2022, 3, 4, 13))
              .find((p) => p.type === 'dayPeriod')
              ?.value?.replace(/\s+/g, ' ');
      }
      meridiemMatch(input) {
          return input.toLowerCase() === this.getLocaleAfternoon().toLowerCase();
      }
      correctHours(time) {
          const { afternoon } = time;
          if (afternoon !== undefined) {
              const { hours } = time;
              if (afternoon) {
                  if (hours < 12) {
                      time.hours += 12;
                  }
              }
              else if (hours === 12) {
                  time.hours = 0;
              }
              delete time.afternoon;
          }
      }
      makeParser(format) {
          format = this.replaceTokens(format, this.localization.dateFormats);
          const matchArray = format.match(this.formattingTokens);
          const { length } = matchArray;
          const expressionArray = [];
          for (let i = 0; i < length; i += 1) {
              const token = matchArray[i];
              const expression = this.expressions[token];
              if (expression?.parser) {
                  expressionArray[i] = expression;
              }
              else {
                  expressionArray[i] = token.replace(/^\[[^[\]]*]$/g, '');
              }
          }
          return (input) => {
              const time = {
                  hours: 0,
                  minutes: 0,
                  seconds: 0,
                  milliseconds: 0,
              };
              for (let i = 0, start = 0; i < length; i += 1) {
                  const token = expressionArray[i];
                  if (typeof token === 'string') {
                      start += token.length;
                  }
                  else {
                      const part = input.slice(start);
                      let value = part;
                      if (token.pattern) {
                          const match = token.pattern.exec(part);
                          value = match[0];
                      }
                      token.parser.call(this, time, value);
                      input = input.replace(value, '');
                  }
              }
              this.correctHours(time);
              return time;
          };
      }
      /**
       * Attempts to create a DateTime from a string.
       * @param input date as string
       * @param localization provides the date template the string is in via the format property
       */
      //eslint-disable-next-line @typescript-eslint/no-unused-vars
      static fromString(input, localization) {
          if (!localization?.format) {
              Namespace.errorMessages.customDateFormatError('No format was provided');
          }
          try {
              const dt = new DateTime();
              dt.setLocalization(localization);
              if (['x', 'X'].indexOf(localization.format) > -1)
                  return new DateTime((localization.format === 'X' ? 1000 : 1) * +input);
              input = input.replace(/\s+/g, ' ');
              const parser = dt.makeParser(localization.format);
              const { year, month, day, hours, minutes, seconds, milliseconds, zone } = parser(input);
              const d = day || (!year && !month ? dt.getDate() : 1);
              const y = year || dt.getFullYear();
              let M = 0;
              if (!(year && !month)) {
                  M = month > 0 ? month - 1 : dt.getMonth();
              }
              if (zone) {
                  return new DateTime(Date.UTC(y, M, d, hours, minutes, seconds, milliseconds + zone.offset * 60 * 1000));
              }
              return new DateTime(y, M, d, hours, minutes, seconds, milliseconds);
          }
          catch (e) {
              Namespace.errorMessages.customDateFormatError(`Unable to parse provided input: ${input}, format: ${localization.format}`);
          }
      }
      /**
       * Returns a string format.
       * See https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/DateTimeFormat
       * for valid templates and locale objects
       * @param template An optional object. If provided, method will use Intl., otherwise the localizations format properties
       * @param locale Can be a string or an array of strings. Uses browser defaults otherwise.
       */
      format(template, locale = this.localization.locale) {
          if (template && typeof template === 'object')
              return new Intl.DateTimeFormat(locale, template).format(this);
          const formatString = this.replaceTokens(
          //try template first
          template ||
              //otherwise try localization format
              this.localization.format ||
              //otherwise try date + time
              `${DefaultFormatLocalization$1.dateFormats.L}, ${DefaultFormatLocalization$1.dateFormats.LT}`, this.localization.dateFormats);
          const formatter = (template) => new Intl.DateTimeFormat(this.localization.locale, template).format(this);
          if (!this.localization.hourCycle)
              this.localization.hourCycle = guessHourCycle(this.localization.locale);
          //if the format asks for a twenty-four-hour string but the hour cycle is not, then make a base guess
          const HHCycle = this.localization.hourCycle.startsWith('h1')
              ? 'h24'
              : this.localization.hourCycle;
          const hhCycle = this.localization.hourCycle.startsWith('h2')
              ? 'h12'
              : this.localization.hourCycle;
          const matches = {
              y: this.year,
              yy: formatter({ year: '2-digit' }),
              yyyy: this.year,
              M: formatter({ month: 'numeric' }),
              MM: this.monthFormatted,
              MMM: this.getAllMonths('short')[this.getMonth()],
              MMMM: this.getAllMonths()[this.getMonth()],
              d: this.date,
              dd: this.dateFormatted,
              ddd: formatter({ weekday: 'short' }),
              dddd: formatter({ weekday: 'long' }),
              H: this.getHours(),
              HH: this.getHoursFormatted(HHCycle),
              h: this.hours > 12 ? this.hours - 12 : this.hours,
              hh: this.getHoursFormatted(hhCycle),
              t: this.meridiem(),
              T: this.meridiem().toUpperCase(),
              m: this.minutes,
              mm: this.minutesFormatted,
              s: this.seconds,
              ss: this.secondsFormatted,
              fff: this.getMilliseconds(),
              // z: this.zoneInformation(dateTime, 'z'), //-4
              // zz: this.zoneInformation(dateTime, 'zz'), //-04
              // zzz: this.zoneInformation(dateTime, 'zzz') //-0400
          };
          return formatString
              .replace(this.dateTimeRegex, (match, $1) => {
              return $1 || matches[match];
          })
              .replace(/\[/g, '')
              .replace(/]/g, '');
      }
  }

  class ServiceLocator {
      constructor() {
          this.cache = new Map();
      }
      locate(identifier) {
          const service = this.cache.get(identifier);
          if (service)
              return service;
          const value = new identifier();
          this.cache.set(identifier, value);
          return value;
      }
  }
  const setupServiceLocator = () => {
      serviceLocator = new ServiceLocator();
  };
  let serviceLocator;

  const CalendarModes = [
      {
          name: 'calendar',
          className: Namespace.css.daysContainer,
          unit: exports.Unit.month,
          step: 1,
      },
      {
          name: 'months',
          className: Namespace.css.monthsContainer,
          unit: exports.Unit.year,
          step: 1,
      },
      {
          name: 'years',
          className: Namespace.css.yearsContainer,
          unit: exports.Unit.year,
          step: 10,
      },
      {
          name: 'decades',
          className: Namespace.css.decadesContainer,
          unit: exports.Unit.year,
          step: 100,
      },
  ];

  class OptionsStore {
      constructor() {
          this._currentCalendarViewMode = 0;
          this._viewDate = new DateTime();
          this.minimumCalendarViewMode = 0;
          this.currentView = 'calendar';
      }
      get currentCalendarViewMode() {
          return this._currentCalendarViewMode;
      }
      set currentCalendarViewMode(value) {
          this._currentCalendarViewMode = value;
          this.currentView = CalendarModes[value].name;
      }
      get viewDate() {
          return this._viewDate;
      }
      set viewDate(v) {
          this._viewDate = v;
          if (this.options)
              this.options.viewDate = v;
      }
      /**
       * When switching back to the calendar from the clock,
       * this sets currentView to the correct calendar view.
       */
      refreshCurrentView() {
          this.currentView = CalendarModes[this.currentCalendarViewMode].name;
      }
      get isTwelveHour() {
          return ['h12', 'h11'].includes(this.options.localization.hourCycle);
      }
  }

  /**
   * Main class for date validation rules based on the options provided.
   */
  class Validation {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
      }
      /**
       * Checks to see if the target date is valid based on the rules provided in the options.
       * Granularity can be provided to check portions of the date instead of the whole.
       * @param targetDate
       * @param granularity
       */
      isValid(targetDate, granularity) {
          if (!this._enabledDisabledDatesIsValid(granularity, targetDate))
              return false;
          if (granularity !== exports.Unit.month &&
              granularity !== exports.Unit.year &&
              this.optionsStore.options.restrictions.daysOfWeekDisabled?.length > 0 &&
              this.optionsStore.options.restrictions.daysOfWeekDisabled.indexOf(targetDate.weekDay) !== -1)
              return false;
          if (!this._minMaxIsValid(granularity, targetDate))
              return false;
          if (granularity === exports.Unit.hours ||
              granularity === exports.Unit.minutes ||
              granularity === exports.Unit.seconds) {
              if (!this._enabledDisabledHoursIsValid(targetDate))
                  return false;
              if (this.optionsStore.options.restrictions.disabledTimeIntervals?.filter((internal) => targetDate.isBetween(internal.from, internal.to)).length !== 0)
                  return false;
          }
          return true;
      }
      _enabledDisabledDatesIsValid(granularity, targetDate) {
          if (granularity !== exports.Unit.date)
              return true;
          if (this.optionsStore.options.restrictions.disabledDates.length > 0 &&
              this._isInDisabledDates(targetDate)) {
              return false;
          }
          // noinspection RedundantIfStatementJS
          if (this.optionsStore.options.restrictions.enabledDates.length > 0 &&
              !this._isInEnabledDates(targetDate)) {
              return false;
          }
          return true;
      }
      /**
       * Checks to see if the disabledDates option is in use and returns true (meaning invalid)
       * if the `testDate` is with in the array. Granularity is by date.
       * @param testDate
       * @private
       */
      _isInDisabledDates(testDate) {
          if (!this.optionsStore.options.restrictions.disabledDates ||
              this.optionsStore.options.restrictions.disabledDates.length === 0)
              return false;
          return !!this.optionsStore.options.restrictions.disabledDates.find((x) => x.isSame(testDate, exports.Unit.date));
      }
      /**
       * Checks to see if the enabledDates option is in use and returns true (meaning valid)
       * if the `testDate` is with in the array. Granularity is by date.
       * @param testDate
       * @private
       */
      _isInEnabledDates(testDate) {
          if (!this.optionsStore.options.restrictions.enabledDates ||
              this.optionsStore.options.restrictions.enabledDates.length === 0)
              return true;
          return !!this.optionsStore.options.restrictions.enabledDates.find((x) => x.isSame(testDate, exports.Unit.date));
      }
      _minMaxIsValid(granularity, targetDate) {
          if (this.optionsStore.options.restrictions.minDate &&
              targetDate.isBefore(this.optionsStore.options.restrictions.minDate, granularity)) {
              return false;
          }
          // noinspection RedundantIfStatementJS
          if (this.optionsStore.options.restrictions.maxDate &&
              targetDate.isAfter(this.optionsStore.options.restrictions.maxDate, granularity)) {
              return false;
          }
          return true;
      }
      _enabledDisabledHoursIsValid(targetDate) {
          if (this.optionsStore.options.restrictions.disabledHours.length > 0 &&
              this._isInDisabledHours(targetDate)) {
              return false;
          }
          // noinspection RedundantIfStatementJS
          if (this.optionsStore.options.restrictions.enabledHours.length > 0 &&
              !this._isInEnabledHours(targetDate)) {
              return false;
          }
          return true;
      }
      /**
       * Checks to see if the disabledHours option is in use and returns true (meaning invalid)
       * if the `testDate` is with in the array. Granularity is by hours.
       * @param testDate
       * @private
       */
      _isInDisabledHours(testDate) {
          if (!this.optionsStore.options.restrictions.disabledHours ||
              this.optionsStore.options.restrictions.disabledHours.length === 0)
              return false;
          const formattedDate = testDate.hours;
          return this.optionsStore.options.restrictions.disabledHours.includes(formattedDate);
      }
      /**
       * Checks to see if the enabledHours option is in use and returns true (meaning valid)
       * if the `testDate` is with in the array. Granularity is by hours.
       * @param testDate
       * @private
       */
      _isInEnabledHours(testDate) {
          if (!this.optionsStore.options.restrictions.enabledHours ||
              this.optionsStore.options.restrictions.enabledHours.length === 0)
              return true;
          const formattedDate = testDate.hours;
          return this.optionsStore.options.restrictions.enabledHours.includes(formattedDate);
      }
      dateRangeIsValid(dates, index, target) {
          // if we're not using the option, then return valid
          if (!this.optionsStore.options.dateRange)
              return true;
          // if we've only selected 0..1 dates, and we're not setting the end date
          // then return valid. We only want to validate the range if both are selected,
          // because the other validation on the target has already occurred.
          if (dates.length !== 2 && index !== 1)
              return true;
          // initialize start date
          const start = dates[0].clone;
          // check if start date is not the same as target date
          if (start.isSame(target, exports.Unit.date))
              return true;
          // add one day to start; start has already been validated
          start.manipulate(1, exports.Unit.date);
          // check each date in the range to make sure it's valid
          while (!start.isSame(target, exports.Unit.date)) {
              const valid = this.isValid(start, exports.Unit.date);
              if (!valid)
                  return false;
              start.manipulate(1, exports.Unit.date);
          }
          return true;
      }
  }

  class EventEmitter {
      constructor() {
          this.subscribers = [];
      }
      subscribe(callback) {
          this.subscribers.push(callback);
          return this.unsubscribe.bind(this, this.subscribers.length - 1);
      }
      unsubscribe(index) {
          this.subscribers.splice(index, 1);
      }
      emit(value) {
          this.subscribers.forEach((callback) => {
              callback(value);
          });
      }
      destroy() {
          this.subscribers = null;
          this.subscribers = [];
      }
  }
  class EventEmitters {
      constructor() {
          this.triggerEvent = new EventEmitter();
          this.viewUpdate = new EventEmitter();
          this.updateDisplay = new EventEmitter();
          this.action = new EventEmitter(); //eslint-disable-line @typescript-eslint/no-explicit-any
          this.updateViewDate = new EventEmitter();
      }
      destroy() {
          this.triggerEvent.destroy();
          this.viewUpdate.destroy();
          this.updateDisplay.destroy();
          this.action.destroy();
          this.updateViewDate.destroy();
      }
  }

  const defaultEnLocalization = {
      clear: 'Clear selection',
      close: 'Close the picker',
      dateFormats: DefaultFormatLocalization$1.dateFormats,
      dayViewHeaderFormat: { month: 'long', year: '2-digit' },
      decrementHour: 'Decrement Hour',
      decrementMinute: 'Decrement Minute',
      decrementSecond: 'Decrement Second',
      format: DefaultFormatLocalization$1.format,
      hourCycle: DefaultFormatLocalization$1.hourCycle,
      incrementHour: 'Increment Hour',
      incrementMinute: 'Increment Minute',
      incrementSecond: 'Increment Second',
      locale: DefaultFormatLocalization$1.locale,
      maxWeekdayLength: 0,
      nextCentury: 'Next Century',
      nextDecade: 'Next Decade',
      nextMonth: 'Next Month',
      nextYear: 'Next Year',
      ordinal: DefaultFormatLocalization$1.ordinal,
      pickHour: 'Pick Hour',
      pickMinute: 'Pick Minute',
      pickSecond: 'Pick Second',
      previousCentury: 'Previous Century',
      previousDecade: 'Previous Decade',
      previousMonth: 'Previous Month',
      previousYear: 'Previous Year',
      selectDate: 'Select Date',
      selectDecade: 'Select Decade',
      selectMonth: 'Select Month',
      selectTime: 'Select Time',
      selectYear: 'Select Year',
      startOfTheWeek: 0,
      today: 'Go to today',
      toggleMeridiem: 'Toggle Meridiem',
  };
  const DefaultOptions = {
      allowInputToggle: false,
      container: undefined,
      dateRange: false,
      debug: false,
      defaultDate: undefined,
      display: {
          icons: {
              type: 'icons',
              time: 'fa-solid fa-clock',
              date: 'fa-solid fa-calendar',
              up: 'fa-solid fa-arrow-up',
              down: 'fa-solid fa-arrow-down',
              previous: 'fa-solid fa-chevron-left',
              next: 'fa-solid fa-chevron-right',
              today: 'fa-solid fa-calendar-check',
              clear: 'fa-solid fa-trash',
              close: 'fa-solid fa-xmark',
          },
          sideBySide: false,
          calendarWeeks: false,
          viewMode: 'calendar',
          toolbarPlacement: 'bottom',
          keepOpen: false,
          buttons: {
              today: false,
              clear: false,
              close: false,
          },
          components: {
              calendar: true,
              date: true,
              month: true,
              year: true,
              decades: true,
              clock: true,
              hours: true,
              minutes: true,
              seconds: false,
              useTwentyfourHour: undefined,
          },
          inline: false,
          theme: 'auto',
          placement: 'bottom',
      },
      keepInvalid: false,
      localization: defaultEnLocalization,
      meta: {},
      multipleDates: false,
      multipleDatesSeparator: '; ',
      promptTimeOnDateChange: false,
      promptTimeOnDateChangeTransitionDelay: 200,
      restrictions: {
          minDate: undefined,
          maxDate: undefined,
          disabledDates: [],
          enabledDates: [],
          daysOfWeekDisabled: [],
          disabledTimeIntervals: [],
          disabledHours: [],
          enabledHours: [],
      },
      stepping: 1,
      useCurrent: true,
      viewDate: new DateTime(),
  };
  const DefaultEnLocalization = { ...defaultEnLocalization };

  /**
   * Attempts to prove `d` is a DateTime or Date or can be converted into one.
   * @param d If a string will attempt creating a date from it.
   * @param localization object containing locale and format settings. Only used with the custom formats
   * @private
   */
  function tryConvertToDateTime(d, localization) {
      if (!d)
          return null;
      if (d.constructor.name === DateTime.name)
          return d;
      if (d.constructor.name === Date.name) {
          return DateTime.convert(d);
      }
      if (typeof d === typeof '') {
          const dateTime = DateTime.fromString(d, localization);
          if (JSON.stringify(dateTime) === 'null') {
              return null;
          }
          return dateTime;
      }
      return null;
  }
  /**
   * Attempts to convert `d` to a DateTime object
   * @param d value to convert
   * @param optionName Provides text to error messages e.g. disabledDates
   * @param localization object containing locale and format settings. Only used with the custom formats
   */
  function convertToDateTime(d, optionName, localization) {
      if (typeof d === typeof '' && optionName !== 'input') {
          Namespace.errorMessages.dateString();
      }
      const converted = tryConvertToDateTime(d, localization);
      if (!converted) {
          Namespace.errorMessages.failedToParseDate(optionName, d, optionName === 'input');
      }
      return converted;
  }
  /**
   * Type checks that `value` is an array of Date or DateTime
   * @param optionName Provides text to error messages e.g. disabledDates
   * @param value Option value
   * @param providedType Used to provide text to error messages
   * @param localization
   */
  function typeCheckDateArray(optionName, value, //eslint-disable-line @typescript-eslint/no-explicit-any
  providedType, localization = DefaultFormatLocalization$1) {
      if (!Array.isArray(value)) {
          Namespace.errorMessages.typeMismatch(optionName, providedType, 'array of DateTime or Date');
      }
      for (let i = 0; i < value.length; i++) {
          const d = value[i];
          const dateTime = convertToDateTime(d, optionName, localization);
          dateTime.setLocalization(localization);
          value[i] = dateTime;
      }
  }
  /**
   * Type checks that `value` is an array of numbers
   * @param optionName Provides text to error messages e.g. disabledDates
   * @param value Option value
   * @param providedType Used to provide text to error messages
   */
  function typeCheckNumberArray(optionName, value, //eslint-disable-line @typescript-eslint/no-explicit-any
  providedType) {
      if (!Array.isArray(value) || value.some((x) => typeof x !== typeof 0)) {
          Namespace.errorMessages.typeMismatch(optionName, providedType, 'array of numbers');
      }
  }

  function mandatoryDate(key) {
      return ({ value, providedType, localization }) => {
          const dateTime = convertToDateTime(value, key, localization);
          if (dateTime !== undefined) {
              dateTime.setLocalization(localization);
              return dateTime;
          }
      };
  }
  function optionalDate(key) {
      const mandatory = mandatoryDate(key);
      return (args) => {
          if (args.value === undefined) {
              return args.value;
          }
          return mandatory(args);
      };
  }
  function numbersInRange(key, lower, upper) {
      return ({ value, providedType }) => {
          if (value === undefined) {
              return [];
          }
          typeCheckNumberArray(key, value, providedType);
          if (value.some((x) => x < lower || x > upper))
              Namespace.errorMessages.numbersOutOfRange(key, lower, upper);
          return value;
      };
  }
  function validHourRange(key) {
      return numbersInRange(key, 0, 23);
  }
  function validDateArray(key) {
      return ({ value, providedType, localization }) => {
          if (value === undefined) {
              return [];
          }
          typeCheckDateArray(key, value, providedType, localization);
          return value;
      };
  }
  function validKeyOption(keyOptions) {
      return ({ value, path }) => {
          if (!keyOptions.includes(value))
              Namespace.errorMessages.unexpectedOptionValue(path.substring(1), value, keyOptions);
          return value;
      };
  }
  const optionProcessors = Object.freeze({
      defaultDate: mandatoryDate('defaultDate'),
      viewDate: mandatoryDate('viewDate'),
      minDate: optionalDate('restrictions.minDate'),
      maxDate: optionalDate('restrictions.maxDate'),
      disabledHours: validHourRange('restrictions.disabledHours'),
      enabledHours: validHourRange('restrictions.enabledHours'),
      disabledDates: validDateArray('restrictions.disabledDates'),
      enabledDates: validDateArray('restrictions.enabledDates'),
      daysOfWeekDisabled: numbersInRange('restrictions.daysOfWeekDisabled', 0, 6),
      disabledTimeIntervals: ({ key, value, providedType, localization }) => {
          if (value === undefined) {
              return [];
          }
          if (!Array.isArray(value)) {
              Namespace.errorMessages.typeMismatch(key, providedType, 'array of { from: DateTime|Date, to: DateTime|Date }');
          }
          const valueObject = value; //eslint-disable-line @typescript-eslint/no-explicit-any
          for (let i = 0; i < valueObject.length; i++) {
              Object.keys(valueObject[i]).forEach((vk) => {
                  const subOptionName = `${key}[${i}].${vk}`;
                  const d = valueObject[i][vk];
                  const dateTime = convertToDateTime(d, subOptionName, localization);
                  dateTime.setLocalization(localization);
                  valueObject[i][vk] = dateTime;
              });
          }
          return valueObject;
      },
      toolbarPlacement: validKeyOption(['top', 'bottom', 'default']),
      type: validKeyOption(['icons', 'sprites']),
      viewMode: validKeyOption([
          'clock',
          'calendar',
          'months',
          'years',
          'decades',
      ]),
      theme: validKeyOption(['light', 'dark', 'auto']),
      placement: validKeyOption(['top', 'bottom']),
      meta: ({ value }) => value,
      dayViewHeaderFormat: ({ value }) => value,
      container: ({ value, path }) => {
          if (value &&
              !(value instanceof HTMLElement ||
                  value instanceof Element ||
                  value?.appendChild)) {
              Namespace.errorMessages.typeMismatch(path.substring(1), typeof value, 'HTMLElement');
          }
          return value;
      },
      useTwentyfourHour: ({ value, path, providedType, defaultType }) => {
          Namespace.errorMessages.deprecatedWarning('useTwentyfourHour', 'Please use "options.localization.hourCycle" instead');
          if (value === undefined || providedType === 'boolean')
              return value;
          Namespace.errorMessages.typeMismatch(path, providedType, defaultType);
      },
      hourCycle: validKeyOption(['h11', 'h12', 'h23', 'h24']),
  });
  const defaultProcessor = ({ value, defaultType, providedType, path, }) => {
      switch (defaultType) {
          case 'boolean':
              return value === 'true' || value === true;
          case 'number':
              return +value;
          case 'string':
              return value.toString();
          case 'object':
              return {};
          case 'function':
              return value;
          default:
              Namespace.errorMessages.typeMismatch(path, providedType, defaultType);
      }
  };
  function processKey(args) {
      return (optionProcessors[args.key] || defaultProcessor)(args);
  }

  class OptionConverter {
      static deepCopy(input) {
          const o = {};
          Object.keys(input).forEach((key) => {
              const inputElement = input[key];
              if (inputElement instanceof DateTime) {
                  o[key] = inputElement.clone;
                  return;
              }
              else if (inputElement instanceof Date) {
                  o[key] = new Date(inputElement.valueOf());
                  return;
              }
              o[key] = inputElement;
              if (typeof inputElement !== 'object' ||
                  inputElement instanceof HTMLElement ||
                  inputElement instanceof Element)
                  return;
              if (!Array.isArray(inputElement)) {
                  o[key] = OptionConverter.deepCopy(inputElement);
              }
          });
          return o;
      }
      /**
       * Finds value out of an object based on a string, period delimited, path
       * @param paths
       * @param obj
       */
      static objectPath(paths, obj) {
          if (paths.charAt(0) === '.')
              paths = paths.slice(1);
          if (!paths)
              return obj;
          return paths
              .split('.')
              .reduce((value, key) => OptionConverter.isValue(value) || OptionConverter.isValue(value[key])
              ? value[key]
              : undefined, obj);
      }
      /**
       * The spread operator caused sub keys to be missing after merging.
       * This is to fix that issue by using spread on the child objects first.
       * Also handles complex options like disabledDates
       * @param provided An option from new providedOptions
       * @param copyTo Destination object. This was added to prevent reference copies
       * @param localization
       * @param path
       */
      static spread(provided, copyTo, localization, path = '') {
          const defaultOptions = OptionConverter.objectPath(path, DefaultOptions);
          const unsupportedOptions = Object.keys(provided).filter((x) => !Object.keys(defaultOptions).includes(x));
          if (unsupportedOptions.length > 0) {
              const flattenedOptions = OptionConverter.getFlattenDefaultOptions();
              const errors = unsupportedOptions.map((x) => {
                  let error = `"${path}.${x}" in not a known option.`;
                  const didYouMean = flattenedOptions.find((y) => y.includes(x));
                  if (didYouMean)
                      error += ` Did you mean "${didYouMean}"?`;
                  return error;
              });
              Namespace.errorMessages.unexpectedOptions(errors);
          }
          Object.keys(provided)
              .filter((key) => key !== '__proto__' && key !== 'constructor')
              .forEach((key) => {
              path += `.${key}`;
              if (path.charAt(0) === '.')
                  path = path.slice(1);
              const defaultOptionValue = defaultOptions[key];
              const providedType = typeof provided[key];
              const defaultType = typeof defaultOptionValue;
              const value = provided[key];
              if (value === undefined || value === null) {
                  copyTo[key] = value;
                  path = path.substring(0, path.lastIndexOf(`.${key}`));
                  return;
              }
              if (typeof defaultOptionValue === 'object' &&
                  !Array.isArray(provided[key]) &&
                  !(defaultOptionValue instanceof Date ||
                      OptionConverter.ignoreProperties.includes(key))) {
                  OptionConverter.spread(provided[key], copyTo[key], localization, path);
              }
              else {
                  copyTo[key] = OptionConverter.processKey(key, value, providedType, defaultType, path, localization);
              }
              path = path.substring(0, path.lastIndexOf(`.${key}`));
          });
      }
      static processKey(key, value, //eslint-disable-line @typescript-eslint/no-explicit-any
      providedType, defaultType, path, localization) {
          return processKey({
              key,
              value,
              providedType,
              defaultType,
              path,
              localization,
          });
      }
      static _mergeOptions(providedOptions, mergeTo) {
          const newConfig = OptionConverter.deepCopy(mergeTo);
          //see if the options specify a locale
          const localization = mergeTo.localization?.locale !== 'default'
              ? mergeTo.localization
              : providedOptions?.localization || DefaultOptions.localization;
          OptionConverter.spread(providedOptions, newConfig, localization, '');
          return newConfig;
      }
      static _dataToOptions(element, options) {
          const eData = JSON.parse(JSON.stringify(element.dataset));
          if (eData?.tdTargetInput)
              delete eData.tdTargetInput;
          if (eData?.tdTargetToggle)
              delete eData.tdTargetToggle;
          if (!eData || Object.keys(eData).length === 0)
              return options;
          const dataOptions = {};
          // because dataset returns camelCase including the 'td' key the option
          // key won't align
          const objectToNormalized = (object) => {
              const lowered = {};
              Object.keys(object).forEach((x) => {
                  lowered[x.toLowerCase()] = x;
              });
              return lowered;
          };
          const normalizeObject = this.normalizeObject(objectToNormalized);
          const optionsLower = objectToNormalized(options);
          Object.keys(eData)
              .filter((x) => x.startsWith(Namespace.dataKey))
              .map((x) => x.substring(2))
              .forEach((key) => {
              let keyOption = optionsLower[key.toLowerCase()];
              // dataset merges dashes to camelCase... yay
              // i.e. key = display_components_seconds
              if (key.includes('_')) {
                  // [display, components, seconds]
                  const split = key.split('_');
                  // display
                  keyOption = optionsLower[split[0].toLowerCase()];
                  if (keyOption !== undefined &&
                      options[keyOption].constructor === Object) {
                      dataOptions[keyOption] = normalizeObject(split, 1, options[keyOption], eData[`td${key}`]);
                  }
              }
              // or key = multipleDate
              else if (keyOption !== undefined) {
                  dataOptions[keyOption] = eData[`td${key}`];
              }
          });
          return this._mergeOptions(dataOptions, options);
      }
      //todo clean this up
      static normalizeObject(objectToNormalized) {
          const normalizeObject = (split, index, optionSubgroup, value) => {
              // first round = display { ... }
              const normalizedOptions = objectToNormalized(optionSubgroup);
              const keyOption = normalizedOptions[split[index].toLowerCase()];
              const internalObject = {};
              if (keyOption === undefined)
                  return internalObject;
              // if this is another object, continue down the rabbit hole
              if (optionSubgroup[keyOption]?.constructor === Object) {
                  index++;
                  internalObject[keyOption] = normalizeObject(split, index, optionSubgroup[keyOption], value);
              }
              else {
                  internalObject[keyOption] = value;
              }
              return internalObject;
          };
          return normalizeObject;
      }
      /**
       * Attempts to prove `d` is a DateTime or Date or can be converted into one.
       * @param d If a string will attempt creating a date from it.
       * @param localization object containing locale and format settings. Only used with the custom formats
       * @private
       */
      static _dateTypeCheck(d, //eslint-disable-line @typescript-eslint/no-explicit-any
      localization) {
          return tryConvertToDateTime(d, localization);
      }
      /**
       * Type checks that `value` is an array of Date or DateTime
       * @param optionName Provides text to error messages e.g. disabledDates
       * @param value Option value
       * @param providedType Used to provide text to error messages
       * @param localization
       */
      static _typeCheckDateArray(optionName, value, providedType, localization) {
          return typeCheckDateArray(optionName, value, providedType, localization);
      }
      /**
       * Type checks that `value` is an array of numbers
       * @param optionName Provides text to error messages e.g. disabledDates
       * @param value Option value
       * @param providedType Used to provide text to error messages
       */
      static _typeCheckNumberArray(optionName, value, providedType) {
          return typeCheckNumberArray(optionName, value, providedType);
      }
      /**
       * Attempts to convert `d` to a DateTime object
       * @param d value to convert
       * @param optionName Provides text to error messages e.g. disabledDates
       * @param localization object containing locale and format settings. Only used with the custom formats
       */
      static dateConversion(d, //eslint-disable-line @typescript-eslint/no-explicit-any
      optionName, localization) {
          return convertToDateTime(d, optionName, localization);
      }
      static getFlattenDefaultOptions() {
          if (this._flattenDefaults)
              return this._flattenDefaults;
          const deepKeys = (t, pre = []) => {
              if (Array.isArray(t))
                  return [];
              if (Object(t) === t) {
                  return Object.entries(t).flatMap(([k, v]) => deepKeys(v, [...pre, k]));
              }
              else {
                  return pre.join('.');
              }
          };
          this._flattenDefaults = deepKeys(DefaultOptions);
          return this._flattenDefaults;
      }
      /**
       * Some options conflict like min/max date. Verify that these kinds of options
       * are set correctly.
       * @param config
       */
      static _validateConflicts(config) {
          if (config.display.sideBySide &&
              (!config.display.components.clock ||
                  !(config.display.components.hours ||
                      config.display.components.minutes ||
                      config.display.components.seconds))) {
              Namespace.errorMessages.conflictingConfiguration('Cannot use side by side mode without the clock components');
          }
          if (config.restrictions.minDate && config.restrictions.maxDate) {
              if (config.restrictions.minDate.isAfter(config.restrictions.maxDate)) {
                  Namespace.errorMessages.conflictingConfiguration('minDate is after maxDate');
              }
              if (config.restrictions.maxDate.isBefore(config.restrictions.minDate)) {
                  Namespace.errorMessages.conflictingConfiguration('maxDate is before minDate');
              }
          }
          if (config.multipleDates && config.dateRange) {
              Namespace.errorMessages.conflictingConfiguration('Cannot uss option "multipleDates" with "dateRange"');
          }
      }
  }
  OptionConverter.ignoreProperties = [
      'meta',
      'dayViewHeaderFormat',
      'container',
      'dateForms',
      'ordinal',
  ];
  OptionConverter.isValue = (a) => a != null; // everything except undefined + null

  class Dates {
      constructor() {
          this._dates = [];
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.validation = serviceLocator.locate(Validation);
          this._eventEmitters = serviceLocator.locate(EventEmitters);
      }
      /**
       * Returns the array of selected dates
       */
      get picked() {
          return [...this._dates];
      }
      /**
       * Returns the last picked value.
       */
      get lastPicked() {
          return this._dates[this.lastPickedIndex]?.clone;
      }
      /**
       * Returns the length of picked dates -1 or 0 if none are selected.
       */
      get lastPickedIndex() {
          if (this._dates.length === 0)
              return 0;
          return this._dates.length - 1;
      }
      /**
       * Formats a DateTime object to a string. Used when setting the input value.
       * @param date
       */
      formatInput(date) {
          if (!date)
              return '';
          date.localization = this.optionsStore.options.localization;
          return date.format();
      }
      /**
       * parse the value into a DateTime object.
       * this can be overwritten to supply your own parsing.
       */
      //eslint-disable-next-line @typescript-eslint/no-explicit-any
      parseInput(value) {
          try {
              return OptionConverter.dateConversion(value, 'input', this.optionsStore.options.localization);
          }
          catch (e) {
              this._eventEmitters.triggerEvent.emit({
                  type: Namespace.events.error,
                  reason: Namespace.errorMessages.failedToParseInput,
                  format: this.optionsStore.options.localization.format,
                  value: value,
              });
              return undefined;
          }
      }
      /**
       * Tries to convert the provided value to a DateTime object.
       * If value is null|undefined then clear the value of the provided index (or 0).
       * @param value Value to convert or null|undefined
       * @param index When using multidates this is the index in the array
       */
      //eslint-disable-next-line @typescript-eslint/no-explicit-any
      setFromInput(value, index) {
          if (!value) {
              this.setValue(undefined, index);
              return;
          }
          const converted = this.parseInput(value);
          if (converted) {
              converted.setLocalization(this.optionsStore.options.localization);
              this.setValue(converted, index);
          }
      }
      /**
       * Adds a new DateTime to selected dates array
       * @param date
       */
      add(date) {
          this._dates.push(date);
      }
      /**
       * Returns true if the `targetDate` is part of the selected dates array.
       * If `unit` is provided then a granularity to that unit will be used.
       * @param targetDate
       * @param unit
       */
      isPicked(targetDate, unit) {
          if (!DateTime.isValid(targetDate))
              return false;
          if (!unit)
              return this._dates.find((x) => x.isSame(targetDate)) !== undefined;
          const format = getFormatByUnit(unit);
          const innerDateFormatted = targetDate.format(format);
          return (this._dates
              .map((x) => x.format(format))
              .find((x) => x === innerDateFormatted) !== undefined);
      }
      /**
       * Returns the index at which `targetDate` is in the array.
       * This is used for updating or removing a date when multi-date is used
       * If `unit` is provided then a granularity to that unit will be used.
       * @param targetDate
       * @param unit
       */
      pickedIndex(targetDate, unit) {
          if (!DateTime.isValid(targetDate))
              return -1;
          if (!unit)
              return this._dates.map((x) => x.valueOf()).indexOf(targetDate.valueOf());
          const format = getFormatByUnit(unit);
          const innerDateFormatted = targetDate.format(format);
          return this._dates.map((x) => x.format(format)).indexOf(innerDateFormatted);
      }
      /**
       * Clears all selected dates.
       */
      clear() {
          this.optionsStore.unset = true;
          this._eventEmitters.triggerEvent.emit({
              type: Namespace.events.change,
              date: undefined,
              oldDate: this.lastPicked,
              isClear: true,
              isValid: true,
          });
          this._dates = [];
          if (this.optionsStore.input)
              this.optionsStore.input.value = '';
          this._eventEmitters.updateDisplay.emit('all');
      }
      /**
       * Find the "book end" years given a `year` and a `factor`
       * @param factor e.g. 100 for decades
       * @param year e.g. 2021
       */
      static getStartEndYear(factor, year) {
          const step = factor / 10, startYear = Math.floor(year / factor) * factor, endYear = startYear + step * 9, focusValue = Math.floor(year / step) * step;
          return [startYear, endYear, focusValue];
      }
      updateInput(target) {
          if (!this.optionsStore.input)
              return;
          let newValue = this.formatInput(target);
          if (this.optionsStore.options.multipleDates ||
              this.optionsStore.options.dateRange) {
              newValue = this._dates
                  .map((d) => this.formatInput(d))
                  .join(this.optionsStore.options.multipleDatesSeparator);
          }
          if (this.optionsStore.input.value != newValue)
              this.optionsStore.input.value = newValue;
      }
      /**
       * Attempts to either clear or set the `target` date at `index`.
       * If the `target` is null then the date will be cleared.
       * If multi-date is being used then it will be removed from the array.
       * If `target` is valid and multi-date is used then if `index` is
       * provided the date at that index will be replaced, otherwise it is appended.
       * @param target
       * @param index
       */
      setValue(target, index) {
          const noIndex = typeof index === 'undefined', isClear = !target && noIndex;
          let oldDate = this.optionsStore.unset ? null : this._dates[index]?.clone;
          if (!oldDate && !this.optionsStore.unset && noIndex && isClear) {
              oldDate = this.lastPicked;
          }
          if (target && oldDate?.isSame(target)) {
              this.updateInput(target);
              return;
          }
          // case of calling setValue(null)
          if (!target) {
              this._setValueNull(isClear, index, oldDate);
              return;
          }
          index = index || 0;
          target = target.clone;
          // minute stepping is being used, force the minute to the closest value
          if (this.optionsStore.options.stepping !== 1) {
              target.minutes =
                  Math.round(target.minutes / this.optionsStore.options.stepping) *
                      this.optionsStore.options.stepping;
              target.startOf(exports.Unit.minutes);
          }
          const onUpdate = (isValid) => {
              this._dates[index] = target;
              this._eventEmitters.updateViewDate.emit(target.clone);
              this.updateInput(target);
              this.optionsStore.unset = false;
              this._eventEmitters.updateDisplay.emit('all');
              this._eventEmitters.triggerEvent.emit({
                  type: Namespace.events.change,
                  date: target,
                  oldDate,
                  isClear,
                  isValid: isValid,
              });
          };
          if (this.validation.isValid(target) &&
              this.validation.dateRangeIsValid(this.picked, index, target)) {
              onUpdate(true);
              return;
          }
          if (this.optionsStore.options.keepInvalid) {
              onUpdate(false);
          }
          this._eventEmitters.triggerEvent.emit({
              type: Namespace.events.error,
              reason: Namespace.errorMessages.failedToSetInvalidDate,
              date: target,
              oldDate,
          });
      }
      _setValueNull(isClear, index, oldDate) {
          if (!this.optionsStore.options.multipleDates ||
              this._dates.length === 1 ||
              isClear) {
              this.optionsStore.unset = true;
              this._dates = [];
          }
          else {
              this._dates.splice(index, 1);
          }
          this.updateInput();
          this._eventEmitters.triggerEvent.emit({
              type: Namespace.events.change,
              date: undefined,
              oldDate,
              isClear,
              isValid: true,
          });
          this._eventEmitters.updateDisplay.emit('all');
      }
  }

  var ActionTypes;
  (function (ActionTypes) {
      ActionTypes["next"] = "next";
      ActionTypes["previous"] = "previous";
      ActionTypes["changeCalendarView"] = "changeCalendarView";
      ActionTypes["selectMonth"] = "selectMonth";
      ActionTypes["selectYear"] = "selectYear";
      ActionTypes["selectDecade"] = "selectDecade";
      ActionTypes["selectDay"] = "selectDay";
      ActionTypes["selectHour"] = "selectHour";
      ActionTypes["selectMinute"] = "selectMinute";
      ActionTypes["selectSecond"] = "selectSecond";
      ActionTypes["incrementHours"] = "incrementHours";
      ActionTypes["incrementMinutes"] = "incrementMinutes";
      ActionTypes["incrementSeconds"] = "incrementSeconds";
      ActionTypes["decrementHours"] = "decrementHours";
      ActionTypes["decrementMinutes"] = "decrementMinutes";
      ActionTypes["decrementSeconds"] = "decrementSeconds";
      ActionTypes["toggleMeridiem"] = "toggleMeridiem";
      ActionTypes["togglePicker"] = "togglePicker";
      ActionTypes["showClock"] = "showClock";
      ActionTypes["showHours"] = "showHours";
      ActionTypes["showMinutes"] = "showMinutes";
      ActionTypes["showSeconds"] = "showSeconds";
      ActionTypes["clear"] = "clear";
      ActionTypes["close"] = "close";
      ActionTypes["today"] = "today";
  })(ActionTypes || (ActionTypes = {}));
  var ActionTypes$1 = ActionTypes;

  /**
   * Creates and updates the grid for `date`
   */
  class DateDisplay {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.dates = serviceLocator.locate(Dates);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the display
       * @private
       */
      getPicker() {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.daysContainer);
          container.append(...this._daysOfTheWeek());
          if (this.optionsStore.options.display.calendarWeeks) {
              const div = document.createElement('div');
              div.classList.add(Namespace.css.calendarWeeks, Namespace.css.noHighlight);
              container.appendChild(div);
          }
          const { rangeHoverEvent, rangeHoverOutEvent } = this.handleMouseEvents(container);
          for (let i = 0; i < 42; i++) {
              if (i !== 0 && i % 7 === 0) {
                  if (this.optionsStore.options.display.calendarWeeks) {
                      const div = document.createElement('div');
                      div.classList.add(Namespace.css.calendarWeeks, Namespace.css.noHighlight);
                      container.appendChild(div);
                  }
              }
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.selectDay);
              container.appendChild(div);
              // if hover is supported then add the events
              if (matchMedia('(hover: hover)').matches &&
                  this.optionsStore.options.dateRange) {
                  div.addEventListener('mouseover', rangeHoverEvent);
                  div.addEventListener('mouseout', rangeHoverOutEvent);
              }
          }
          return container;
      }
      /**
       * Populates the grid and updates enabled states
       * @private
       */
      _update(widget, paint) {
          const container = widget.getElementsByClassName(Namespace.css.daysContainer)[0];
          this._updateCalendarView(container);
          const innerDate = this.optionsStore.viewDate.clone
              .startOf(exports.Unit.month)
              .startOf('weekDay', this.optionsStore.options.localization.startOfTheWeek)
              .manipulate(12, exports.Unit.hours);
          this._handleCalendarWeeks(container, innerDate.clone);
          container
              .querySelectorAll(`[data-action="${ActionTypes$1.selectDay}"]`)
              .forEach((element) => {
              const classes = [];
              classes.push(Namespace.css.day);
              if (innerDate.isBefore(this.optionsStore.viewDate, exports.Unit.month)) {
                  classes.push(Namespace.css.old);
              }
              if (innerDate.isAfter(this.optionsStore.viewDate, exports.Unit.month)) {
                  classes.push(Namespace.css.new);
              }
              if (!this.optionsStore.unset &&
                  !this.optionsStore.options.dateRange &&
                  this.dates.isPicked(innerDate, exports.Unit.date)) {
                  classes.push(Namespace.css.active);
              }
              if (!this.validation.isValid(innerDate, exports.Unit.date)) {
                  classes.push(Namespace.css.disabled);
              }
              if (innerDate.isSame(new DateTime(), exports.Unit.date)) {
                  classes.push(Namespace.css.today);
              }
              if (innerDate.weekDay === 0 || innerDate.weekDay === 6) {
                  classes.push(Namespace.css.weekend);
              }
              this._handleDateRange(innerDate, classes);
              paint(exports.Unit.date, innerDate, classes, element);
              element.classList.remove(...element.classList);
              element.classList.add(...classes);
              element.setAttribute('data-value', this._dateToDataValue(innerDate));
              element.setAttribute('data-day', `${innerDate.date}`);
              element.innerText = innerDate.parts(undefined, {
                  day: 'numeric',
              }).day;
              innerDate.manipulate(1, exports.Unit.date);
          });
      }
      _dateToDataValue(date) {
          if (!DateTime.isValid(date))
              return '';
          return `${date.year}-${date.month.toString().padStart(2, '0')}-${date.date
            .toString()
            .padStart(2, '0')}`;
      }
      _handleDateRange(innerDate, classes) {
          const rangeStart = this.dates.picked[0];
          const rangeEnd = this.dates.picked[1];
          if (this.optionsStore.options.dateRange) {
              if (innerDate.isBetween(rangeStart, rangeEnd, exports.Unit.date)) {
                  classes.push(Namespace.css.rangeIn);
              }
              if (innerDate.isSame(rangeStart, exports.Unit.date)) {
                  classes.push(Namespace.css.rangeStart);
              }
              if (innerDate.isSame(rangeEnd, exports.Unit.date)) {
                  classes.push(Namespace.css.rangeEnd);
              }
          }
      }
      handleMouseEvents(container) {
          const rangeHoverEvent = (e) => {
              const currentTarget = e?.currentTarget;
              // if we have 0 or 2 selected or if the target is disabled then ignore
              if (this.dates.picked.length !== 1 ||
                  currentTarget.classList.contains(Namespace.css.disabled))
                  return;
              // select all the date divs
              const allDays = [...container.querySelectorAll('.day')];
              // get the date value from the element being hovered over
              const attributeValue = currentTarget.getAttribute('data-value');
              // format the string to a date
              const innerDate = DateTime.fromString(attributeValue, {
                  format: 'yyyy-MM-dd',
              });
              // find the position of the target in the date container
              const dayIndex = allDays.findIndex((e) => e.getAttribute('data-value') === attributeValue);
              // find the first and second selected dates
              const rangeStart = this.dates.picked[0];
              const rangeEnd = this.dates.picked[1];
              //format the start date so that it can be found by the attribute
              const rangeStartFormatted = this._dateToDataValue(rangeStart);
              const rangeStartIndex = allDays.findIndex((e) => e.getAttribute('data-value') === rangeStartFormatted);
              const rangeStartElement = allDays[rangeStartIndex];
              //make sure we don't leave start/end classes if we don't need them
              if (!innerDate.isSame(rangeStart, exports.Unit.date)) {
                  currentTarget.classList.remove(Namespace.css.rangeStart);
              }
              if (!innerDate.isSame(rangeEnd, exports.Unit.date)) {
                  currentTarget.classList.remove(Namespace.css.rangeEnd);
              }
              // the following figures out which direct from start date is selected
              // the selection "cap" classes are applied if needed
              // otherwise all the dates between will get the `rangeIn` class.
              // We make this selection based on the element's index and the rangeStart index
              let lambda;
              if (innerDate.isBefore(rangeStart)) {
                  currentTarget.classList.add(Namespace.css.rangeStart);
                  rangeStartElement?.classList.remove(Namespace.css.rangeStart);
                  rangeStartElement?.classList.add(Namespace.css.rangeEnd);
                  lambda = (_, index) => index > dayIndex && index < rangeStartIndex;
              }
              else {
                  currentTarget.classList.add(Namespace.css.rangeEnd);
                  rangeStartElement?.classList.remove(Namespace.css.rangeEnd);
                  rangeStartElement?.classList.add(Namespace.css.rangeStart);
                  lambda = (_, index) => index < dayIndex && index > rangeStartIndex;
              }
              allDays.filter(lambda).forEach((e) => {
                  e.classList.add(Namespace.css.rangeIn);
              });
          };
          const rangeHoverOutEvent = (e) => {
              // find all the dates in the container
              const allDays = [...container.querySelectorAll('.day')];
              // if only the start is selected, remove all the rangeIn classes
              // we do this because once the user hovers over a new date the range will be recalculated.
              if (this.dates.picked.length === 1)
                  allDays.forEach((e) => e.classList.remove(Namespace.css.rangeIn));
              // if we have 0 or 2 dates selected then ignore
              if (this.dates.picked.length !== 1)
                  return;
              const currentTarget = e?.currentTarget;
              // get the elements date from the attribute value
              const innerDate = new DateTime(currentTarget.getAttribute('data-value'));
              // verify selections and remove invalid classes
              if (!innerDate.isSame(this.dates.picked[0], exports.Unit.date)) {
                  currentTarget.classList.remove(Namespace.css.rangeStart);
              }
              if (!innerDate.isSame(this.dates.picked[1], exports.Unit.date)) {
                  currentTarget.classList.remove(Namespace.css.rangeEnd);
              }
          };
          return { rangeHoverEvent, rangeHoverOutEvent };
      }
      _updateCalendarView(container) {
          if (this.optionsStore.currentView !== 'calendar')
              return;
          const [previous, switcher, next] = container.parentElement
              .getElementsByClassName(Namespace.css.calendarHeader)[0]
              .getElementsByTagName('div');
          switcher.setAttribute(Namespace.css.daysContainer, this.optionsStore.viewDate.format(this.optionsStore.options.localization.dayViewHeaderFormat));
          this.optionsStore.options.display.components.month
              ? switcher.classList.remove(Namespace.css.disabled)
              : switcher.classList.add(Namespace.css.disabled);
          this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(-1, exports.Unit.month), exports.Unit.month)
              ? previous.classList.remove(Namespace.css.disabled)
              : previous.classList.add(Namespace.css.disabled);
          this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(1, exports.Unit.month), exports.Unit.month)
              ? next.classList.remove(Namespace.css.disabled)
              : next.classList.add(Namespace.css.disabled);
      }
      /***
       * Generates a html row that contains the days of the week.
       * @private
       */
      _daysOfTheWeek() {
          const innerDate = this.optionsStore.viewDate.clone
              .startOf('weekDay', this.optionsStore.options.localization.startOfTheWeek)
              .startOf(exports.Unit.date);
          const row = [];
          document.createElement('div');
          if (this.optionsStore.options.display.calendarWeeks) {
              const htmlDivElement = document.createElement('div');
              htmlDivElement.classList.add(Namespace.css.calendarWeeks, Namespace.css.noHighlight);
              htmlDivElement.innerText = '#';
              row.push(htmlDivElement);
          }
          for (let i = 0; i < 7; i++) {
              const htmlDivElement = document.createElement('div');
              htmlDivElement.classList.add(Namespace.css.dayOfTheWeek, Namespace.css.noHighlight);
              let weekDay = innerDate.format({ weekday: 'short' });
              if (this.optionsStore.options.localization.maxWeekdayLength > 0)
                  weekDay = weekDay.substring(0, this.optionsStore.options.localization.maxWeekdayLength);
              htmlDivElement.innerText = weekDay;
              innerDate.manipulate(1, exports.Unit.date);
              row.push(htmlDivElement);
          }
          return row;
      }
      _handleCalendarWeeks(container, innerDate) {
          [...container.querySelectorAll(`.${Namespace.css.calendarWeeks}`)]
              .filter((e) => e.innerText !== '#')
              .forEach((element) => {
              element.innerText = `${innerDate.week}`;
              innerDate.manipulate(7, exports.Unit.date);
          });
      }
  }

  /**
   * Creates and updates the grid for `month`
   */
  class MonthDisplay {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.dates = serviceLocator.locate(Dates);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the display
       * @private
       */
      getPicker() {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.monthsContainer);
          for (let i = 0; i < 12; i++) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.selectMonth);
              container.appendChild(div);
          }
          return container;
      }
      /**
       * Populates the grid and updates enabled states
       * @private
       */
      _update(widget, paint) {
          const container = widget.getElementsByClassName(Namespace.css.monthsContainer)[0];
          if (this.optionsStore.currentView === 'months') {
              const [previous, switcher, next] = container.parentElement
                  .getElementsByClassName(Namespace.css.calendarHeader)[0]
                  .getElementsByTagName('div');
              switcher.setAttribute(Namespace.css.monthsContainer, this.optionsStore.viewDate.format({ year: 'numeric' }));
              this.optionsStore.options.display.components.year
                  ? switcher.classList.remove(Namespace.css.disabled)
                  : switcher.classList.add(Namespace.css.disabled);
              this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(-1, exports.Unit.year), exports.Unit.year)
                  ? previous.classList.remove(Namespace.css.disabled)
                  : previous.classList.add(Namespace.css.disabled);
              this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(1, exports.Unit.year), exports.Unit.year)
                  ? next.classList.remove(Namespace.css.disabled)
                  : next.classList.add(Namespace.css.disabled);
          }
          const innerDate = this.optionsStore.viewDate.clone.startOf(exports.Unit.year);
          container
              .querySelectorAll(`[data-action="${ActionTypes$1.selectMonth}"]`)
              .forEach((containerClone, index) => {
              const classes = [];
              classes.push(Namespace.css.month);
              if (!this.optionsStore.unset &&
                  this.dates.isPicked(innerDate, exports.Unit.month)) {
                  classes.push(Namespace.css.active);
              }
              if (!this.validation.isValid(innerDate, exports.Unit.month)) {
                  classes.push(Namespace.css.disabled);
              }
              paint(exports.Unit.month, innerDate, classes, containerClone);
              containerClone.classList.remove(...containerClone.classList);
              containerClone.classList.add(...classes);
              containerClone.setAttribute('data-value', `${index}`);
              containerClone.innerText = `${innerDate.format({ month: 'short' })}`;
              innerDate.manipulate(1, exports.Unit.month);
          });
      }
  }

  /**
   * Creates and updates the grid for `year`
   */
  class YearDisplay {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.dates = serviceLocator.locate(Dates);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the display
       * @private
       */
      getPicker() {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.yearsContainer);
          for (let i = 0; i < 12; i++) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.selectYear);
              container.appendChild(div);
          }
          return container;
      }
      /**
       * Populates the grid and updates enabled states
       * @private
       */
      _update(widget, paint) {
          this._startYear = this.optionsStore.viewDate.clone.manipulate(-1, exports.Unit.year);
          this._endYear = this.optionsStore.viewDate.clone.manipulate(10, exports.Unit.year);
          const container = widget.getElementsByClassName(Namespace.css.yearsContainer)[0];
          if (this.optionsStore.currentView === 'years') {
              const [previous, switcher, next] = container.parentElement
                  .getElementsByClassName(Namespace.css.calendarHeader)[0]
                  .getElementsByTagName('div');
              switcher.setAttribute(Namespace.css.yearsContainer, `${this._startYear.format({ year: 'numeric' })}-${this._endYear.format({
                year: 'numeric',
            })}`);
              this.optionsStore.options.display.components.decades
                  ? switcher.classList.remove(Namespace.css.disabled)
                  : switcher.classList.add(Namespace.css.disabled);
              this.validation.isValid(this._startYear, exports.Unit.year)
                  ? previous.classList.remove(Namespace.css.disabled)
                  : previous.classList.add(Namespace.css.disabled);
              this.validation.isValid(this._endYear, exports.Unit.year)
                  ? next.classList.remove(Namespace.css.disabled)
                  : next.classList.add(Namespace.css.disabled);
          }
          const innerDate = this.optionsStore.viewDate.clone
              .startOf(exports.Unit.year)
              .manipulate(-1, exports.Unit.year);
          container
              .querySelectorAll(`[data-action="${ActionTypes$1.selectYear}"]`)
              .forEach((containerClone) => {
              const classes = [];
              classes.push(Namespace.css.year);
              if (!this.optionsStore.unset &&
                  this.dates.isPicked(innerDate, exports.Unit.year)) {
                  classes.push(Namespace.css.active);
              }
              if (!this.validation.isValid(innerDate, exports.Unit.year)) {
                  classes.push(Namespace.css.disabled);
              }
              paint(exports.Unit.year, innerDate, classes, containerClone);
              containerClone.classList.remove(...containerClone.classList);
              containerClone.classList.add(...classes);
              containerClone.setAttribute('data-value', `${innerDate.year}`);
              containerClone.innerText = innerDate.format({ year: 'numeric' });
              innerDate.manipulate(1, exports.Unit.year);
          });
      }
  }

  /**
   * Creates and updates the grid for `seconds`
   */
  class DecadeDisplay {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.dates = serviceLocator.locate(Dates);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the display
       * @private
       */
      getPicker() {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.decadesContainer);
          for (let i = 0; i < 12; i++) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.selectDecade);
              container.appendChild(div);
          }
          return container;
      }
      /**
       * Populates the grid and updates enabled states
       * @private
       */
      _update(widget, paint) {
          const [start, end] = Dates.getStartEndYear(100, this.optionsStore.viewDate.year);
          this._startDecade = this.optionsStore.viewDate.clone.startOf(exports.Unit.year);
          this._startDecade.year = start;
          this._endDecade = this.optionsStore.viewDate.clone.startOf(exports.Unit.year);
          this._endDecade.year = end;
          const container = widget.getElementsByClassName(Namespace.css.decadesContainer)[0];
          const [previous, switcher, next] = container.parentElement
              .getElementsByClassName(Namespace.css.calendarHeader)[0]
              .getElementsByTagName('div');
          if (this.optionsStore.currentView === 'decades') {
              switcher.setAttribute(Namespace.css.decadesContainer, `${this._startDecade.format({
                year: 'numeric',
            })}-${this._endDecade.format({ year: 'numeric' })}`);
              this.validation.isValid(this._startDecade, exports.Unit.year)
                  ? previous.classList.remove(Namespace.css.disabled)
                  : previous.classList.add(Namespace.css.disabled);
              this.validation.isValid(this._endDecade, exports.Unit.year)
                  ? next.classList.remove(Namespace.css.disabled)
                  : next.classList.add(Namespace.css.disabled);
          }
          const pickedYears = this.dates.picked.map((x) => x.year);
          container
              .querySelectorAll(`[data-action="${ActionTypes$1.selectDecade}"]`)
              .forEach((containerClone, index) => {
              if (index === 0) {
                  containerClone.classList.add(Namespace.css.old);
                  if (this._startDecade.year - 10 < 0) {
                      containerClone.textContent = ' ';
                      previous.classList.add(Namespace.css.disabled);
                      containerClone.classList.add(Namespace.css.disabled);
                      containerClone.setAttribute('data-value', '');
                      return;
                  }
                  else {
                      containerClone.innerText = this._startDecade.clone
                          .manipulate(-10, exports.Unit.year)
                          .format({ year: 'numeric' });
                      containerClone.setAttribute('data-value', `${this._startDecade.year}`);
                      return;
                  }
              }
              const classes = [];
              classes.push(Namespace.css.decade);
              const startDecadeYear = this._startDecade.year;
              const endDecadeYear = this._startDecade.year + 9;
              if (!this.optionsStore.unset &&
                  pickedYears.filter((x) => x >= startDecadeYear && x <= endDecadeYear)
                      .length > 0) {
                  classes.push(Namespace.css.active);
              }
              if (!this.validation.isValid(this._startDecade, exports.Unit.year) &&
                  !this.validation.isValid(this._startDecade.clone.manipulate(10, exports.Unit.year), exports.Unit.year)) {
                  classes.push(Namespace.css.disabled);
              }
              paint('decade', this._startDecade, classes, containerClone);
              containerClone.classList.remove(...containerClone.classList);
              containerClone.classList.add(...classes);
              containerClone.setAttribute('data-value', `${this._startDecade.year}`);
              containerClone.innerText = `${this._startDecade.format({
                year: 'numeric',
            })}`;
              this._startDecade.manipulate(10, exports.Unit.year);
          });
      }
  }

  /**
   * Creates the clock display
   */
  class TimeDisplay {
      constructor() {
          this._gridColumns = '';
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.dates = serviceLocator.locate(Dates);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the clock display
       * @private
       */
      getPicker(iconTag) {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.clockContainer);
          container.append(...this._grid(iconTag));
          return container;
      }
      /**
       * Populates the various elements with in the clock display
       * like the current hour and if the manipulation icons are enabled.
       * @private
       */
      _update(widget) {
          const timesDiv = (widget.getElementsByClassName(Namespace.css.clockContainer)[0]);
          let lastPicked = this.dates.lastPicked?.clone;
          if (!lastPicked && this.optionsStore.options.useCurrent)
              lastPicked = this.optionsStore.viewDate.clone;
          timesDiv
              .querySelectorAll('.disabled')
              .forEach((element) => element.classList.remove(Namespace.css.disabled));
          if (this.optionsStore.options.display.components.hours) {
              if (!this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(1, exports.Unit.hours), exports.Unit.hours)) {
                  timesDiv
                      .querySelector(`[data-action=${ActionTypes$1.incrementHours}]`)
                      .classList.add(Namespace.css.disabled);
              }
              if (!this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(-1, exports.Unit.hours), exports.Unit.hours)) {
                  timesDiv
                      .querySelector(`[data-action=${ActionTypes$1.decrementHours}]`)
                      .classList.add(Namespace.css.disabled);
              }
              timesDiv.querySelector(`[data-time-component=${exports.Unit.hours}]`).innerText = lastPicked
                  ? lastPicked.getHoursFormatted(this.optionsStore.options.localization.hourCycle)
                  : '--';
          }
          if (this.optionsStore.options.display.components.minutes) {
              if (!this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(1, exports.Unit.minutes), exports.Unit.minutes)) {
                  timesDiv
                      .querySelector(`[data-action=${ActionTypes$1.incrementMinutes}]`)
                      .classList.add(Namespace.css.disabled);
              }
              if (!this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(-1, exports.Unit.minutes), exports.Unit.minutes)) {
                  timesDiv
                      .querySelector(`[data-action=${ActionTypes$1.decrementMinutes}]`)
                      .classList.add(Namespace.css.disabled);
              }
              timesDiv.querySelector(`[data-time-component=${exports.Unit.minutes}]`).innerText = lastPicked ? lastPicked.minutesFormatted : '--';
          }
          if (this.optionsStore.options.display.components.seconds) {
              if (!this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(1, exports.Unit.seconds), exports.Unit.seconds)) {
                  timesDiv
                      .querySelector(`[data-action=${ActionTypes$1.incrementSeconds}]`)
                      .classList.add(Namespace.css.disabled);
              }
              if (!this.validation.isValid(this.optionsStore.viewDate.clone.manipulate(-1, exports.Unit.seconds), exports.Unit.seconds)) {
                  timesDiv
                      .querySelector(`[data-action=${ActionTypes$1.decrementSeconds}]`)
                      .classList.add(Namespace.css.disabled);
              }
              timesDiv.querySelector(`[data-time-component=${exports.Unit.seconds}]`).innerText = lastPicked ? lastPicked.secondsFormatted : '--';
          }
          if (this.optionsStore.isTwelveHour) {
              const toggle = timesDiv.querySelector(`[data-action=${ActionTypes$1.toggleMeridiem}]`);
              const meridiemDate = (lastPicked || this.optionsStore.viewDate).clone;
              toggle.innerText = meridiemDate.meridiem();
              if (!this.validation.isValid(meridiemDate.manipulate(meridiemDate.hours >= 12 ? -12 : 12, exports.Unit.hours))) {
                  toggle.classList.add(Namespace.css.disabled);
              }
              else {
                  toggle.classList.remove(Namespace.css.disabled);
              }
          }
          timesDiv.style.gridTemplateAreas = `"${this._gridColumns}"`;
      }
      /**
       * Creates the table for the clock display depending on what options are selected.
       * @private
       */
      _grid(iconTag) {
          this._gridColumns = '';
          const top = [], middle = [], bottom = [], separator = document.createElement('div'), upIcon = iconTag(this.optionsStore.options.display.icons.up), downIcon = iconTag(this.optionsStore.options.display.icons.down);
          separator.classList.add(Namespace.css.separator, Namespace.css.noHighlight);
          const separatorColon = separator.cloneNode(true);
          separatorColon.innerHTML = ':';
          const getSeparator = (colon = false) => {
              return colon
                  ? separatorColon.cloneNode(true)
                  : separator.cloneNode(true);
          };
          if (this.optionsStore.options.display.components.hours) {
              let divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.incrementHour);
              divElement.setAttribute('data-action', ActionTypes$1.incrementHours);
              divElement.appendChild(upIcon.cloneNode(true));
              top.push(divElement);
              divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.pickHour);
              divElement.setAttribute('data-action', ActionTypes$1.showHours);
              divElement.setAttribute('data-time-component', exports.Unit.hours);
              middle.push(divElement);
              divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.decrementHour);
              divElement.setAttribute('data-action', ActionTypes$1.decrementHours);
              divElement.appendChild(downIcon.cloneNode(true));
              bottom.push(divElement);
              this._gridColumns += 'a';
          }
          if (this.optionsStore.options.display.components.minutes) {
              this._gridColumns += ' a';
              if (this.optionsStore.options.display.components.hours) {
                  top.push(getSeparator());
                  middle.push(getSeparator(true));
                  bottom.push(getSeparator());
                  this._gridColumns += ' a';
              }
              let divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.incrementMinute);
              divElement.setAttribute('data-action', ActionTypes$1.incrementMinutes);
              divElement.appendChild(upIcon.cloneNode(true));
              top.push(divElement);
              divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.pickMinute);
              divElement.setAttribute('data-action', ActionTypes$1.showMinutes);
              divElement.setAttribute('data-time-component', exports.Unit.minutes);
              middle.push(divElement);
              divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.decrementMinute);
              divElement.setAttribute('data-action', ActionTypes$1.decrementMinutes);
              divElement.appendChild(downIcon.cloneNode(true));
              bottom.push(divElement);
          }
          if (this.optionsStore.options.display.components.seconds) {
              this._gridColumns += ' a';
              if (this.optionsStore.options.display.components.minutes) {
                  top.push(getSeparator());
                  middle.push(getSeparator(true));
                  bottom.push(getSeparator());
                  this._gridColumns += ' a';
              }
              let divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.incrementSecond);
              divElement.setAttribute('data-action', ActionTypes$1.incrementSeconds);
              divElement.appendChild(upIcon.cloneNode(true));
              top.push(divElement);
              divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.pickSecond);
              divElement.setAttribute('data-action', ActionTypes$1.showSeconds);
              divElement.setAttribute('data-time-component', exports.Unit.seconds);
              middle.push(divElement);
              divElement = document.createElement('div');
              divElement.setAttribute('title', this.optionsStore.options.localization.decrementSecond);
              divElement.setAttribute('data-action', ActionTypes$1.decrementSeconds);
              divElement.appendChild(downIcon.cloneNode(true));
              bottom.push(divElement);
          }
          if (this.optionsStore.isTwelveHour) {
              this._gridColumns += ' a';
              let divElement = getSeparator();
              top.push(divElement);
              const button = document.createElement('button');
              button.setAttribute('type', 'button');
              button.setAttribute('title', this.optionsStore.options.localization.toggleMeridiem);
              button.setAttribute('data-action', ActionTypes$1.toggleMeridiem);
              button.setAttribute('tabindex', '-1');
              if (Namespace.css.toggleMeridiem.includes(',')) {
                  //todo move this to paint function?
                  button.classList.add(...Namespace.css.toggleMeridiem.split(','));
              }
              else
                  button.classList.add(Namespace.css.toggleMeridiem);
              divElement = document.createElement('div');
              divElement.classList.add(Namespace.css.noHighlight);
              divElement.appendChild(button);
              middle.push(divElement);
              divElement = getSeparator();
              bottom.push(divElement);
          }
          this._gridColumns = this._gridColumns.trim();
          return [...top, ...middle, ...bottom];
      }
  }

  /**
   * Creates and updates the grid for `hours`
   */
  class HourDisplay {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the display
       * @private
       */
      getPicker() {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.hourContainer);
          for (let i = 0; i < (this.optionsStore.isTwelveHour ? 12 : 24); i++) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.selectHour);
              container.appendChild(div);
          }
          return container;
      }
      /**
       * Populates the grid and updates enabled states
       * @private
       */
      _update(widget, paint) {
          const container = widget.getElementsByClassName(Namespace.css.hourContainer)[0];
          const innerDate = this.optionsStore.viewDate.clone.startOf(exports.Unit.date);
          container
              .querySelectorAll(`[data-action="${ActionTypes$1.selectHour}"]`)
              .forEach((containerClone) => {
              const classes = [];
              classes.push(Namespace.css.hour);
              if (!this.validation.isValid(innerDate, exports.Unit.hours)) {
                  classes.push(Namespace.css.disabled);
              }
              paint(exports.Unit.hours, innerDate, classes, containerClone);
              containerClone.classList.remove(...containerClone.classList);
              containerClone.classList.add(...classes);
              containerClone.setAttribute('data-value', `${innerDate.hours}`);
              containerClone.innerText = innerDate.getHoursFormatted(this.optionsStore.options.localization.hourCycle);
              innerDate.manipulate(1, exports.Unit.hours);
          });
      }
  }

  /**
   * Creates and updates the grid for `minutes`
   */
  class MinuteDisplay {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the display
       * @private
       */
      getPicker() {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.minuteContainer);
          const step = this.optionsStore.options.stepping === 1
              ? 5
              : this.optionsStore.options.stepping;
          for (let i = 0; i < 60 / step; i++) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.selectMinute);
              container.appendChild(div);
          }
          return container;
      }
      /**
       * Populates the grid and updates enabled states
       * @private
       */
      _update(widget, paint) {
          const container = widget.getElementsByClassName(Namespace.css.minuteContainer)[0];
          const innerDate = this.optionsStore.viewDate.clone.startOf(exports.Unit.hours);
          const step = this.optionsStore.options.stepping === 1
              ? 5
              : this.optionsStore.options.stepping;
          container
              .querySelectorAll(`[data-action="${ActionTypes$1.selectMinute}"]`)
              .forEach((containerClone) => {
              const classes = [];
              classes.push(Namespace.css.minute);
              if (!this.validation.isValid(innerDate, exports.Unit.minutes)) {
                  classes.push(Namespace.css.disabled);
              }
              paint(exports.Unit.minutes, innerDate, classes, containerClone);
              containerClone.classList.remove(...containerClone.classList);
              containerClone.classList.add(...classes);
              containerClone.setAttribute('data-value', `${innerDate.minutes}`);
              containerClone.innerText = innerDate.minutesFormatted;
              innerDate.manipulate(step, exports.Unit.minutes);
          });
      }
  }

  /**
   * Creates and updates the grid for `seconds`
   */
  class secondDisplay {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.validation = serviceLocator.locate(Validation);
      }
      /**
       * Build the container html for the display
       * @private
       */
      getPicker() {
          const container = document.createElement('div');
          container.classList.add(Namespace.css.secondContainer);
          for (let i = 0; i < 12; i++) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.selectSecond);
              container.appendChild(div);
          }
          return container;
      }
      /**
       * Populates the grid and updates enabled states
       * @private
       */
      _update(widget, paint) {
          const container = widget.getElementsByClassName(Namespace.css.secondContainer)[0];
          const innerDate = this.optionsStore.viewDate.clone.startOf(exports.Unit.minutes);
          container
              .querySelectorAll(`[data-action="${ActionTypes$1.selectSecond}"]`)
              .forEach((containerClone) => {
              const classes = [];
              classes.push(Namespace.css.second);
              if (!this.validation.isValid(innerDate, exports.Unit.seconds)) {
                  classes.push(Namespace.css.disabled);
              }
              paint(exports.Unit.seconds, innerDate, classes, containerClone);
              containerClone.classList.remove(...containerClone.classList);
              containerClone.classList.add(...classes);
              containerClone.setAttribute('data-value', `${innerDate.seconds}`);
              containerClone.innerText = innerDate.secondsFormatted;
              innerDate.manipulate(5, exports.Unit.seconds);
          });
      }
  }

  /**
   * Provides a collapse functionality to the view changes
   */
  class Collapse {
      /**
       * Flips the show/hide state of `target`
       * @param target html element to affect.
       */
      static toggle(target) {
          if (target.classList.contains(Namespace.css.show)) {
              this.hide(target);
          }
          else {
              this.show(target);
          }
      }
      /**
       * Skips any animation or timeouts and immediately set the element to show.
       * @param target
       */
      static showImmediately(target) {
          target.classList.remove(Namespace.css.collapsing);
          target.classList.add(Namespace.css.collapse, Namespace.css.show);
          target.style.height = '';
      }
      /**
       * If `target` is not already showing, then show after the animation.
       * @param target
       */
      static show(target) {
          if (target.classList.contains(Namespace.css.collapsing) ||
              target.classList.contains(Namespace.css.show))
              return;
          const complete = () => {
              Collapse.showImmediately(target);
          };
          target.style.height = '0';
          target.classList.remove(Namespace.css.collapse);
          target.classList.add(Namespace.css.collapsing);
          //eslint-disable-next-line @typescript-eslint/no-unused-vars
          setTimeout(complete, this.getTransitionDurationFromElement(target));
          target.style.height = `${target.scrollHeight}px`;
      }
      /**
       * Skips any animation or timeouts and immediately set the element to hide.
       * @param target
       */
      static hideImmediately(target) {
          if (!target)
              return;
          target.classList.remove(Namespace.css.collapsing, Namespace.css.show);
          target.classList.add(Namespace.css.collapse);
      }
      /**
       * If `target` is not already hidden, then hide after the animation.
       * @param target HTML Element
       */
      static hide(target) {
          if (target.classList.contains(Namespace.css.collapsing) ||
              !target.classList.contains(Namespace.css.show))
              return;
          const complete = () => {
              Collapse.hideImmediately(target);
          };
          target.style.height = `${target.getBoundingClientRect()['height']}px`;
          const reflow = (element) => element.offsetHeight;
          reflow(target);
          target.classList.remove(Namespace.css.collapse, Namespace.css.show);
          target.classList.add(Namespace.css.collapsing);
          target.style.height = '';
          //eslint-disable-next-line @typescript-eslint/no-unused-vars
          setTimeout(complete, this.getTransitionDurationFromElement(target));
      }
  }
  /**
   * Gets the transition duration from the `element` by getting css properties
   * `transition-duration` and `transition-delay`
   * @param element HTML Element
   */
  Collapse.getTransitionDurationFromElement = (element) => {
      if (!element) {
          return 0;
      }
      // Get transition-duration of the element
      let { transitionDuration, transitionDelay } = window.getComputedStyle(element);
      const floatTransitionDuration = Number.parseFloat(transitionDuration);
      const floatTransitionDelay = Number.parseFloat(transitionDelay);
      // Return 0 if element or transition duration is not found
      if (!floatTransitionDuration && !floatTransitionDelay) {
          return 0;
      }
      // If multiple durations are defined, take the first
      transitionDuration = transitionDuration.split(',')[0];
      transitionDelay = transitionDelay.split(',')[0];
      return ((Number.parseFloat(transitionDuration) +
          Number.parseFloat(transitionDelay)) *
          1000);
  };

  /**
   * Main class for all things display related.
   */
  class Display {
      constructor() {
          this._isVisible = false;
          /**
           * A document click event to hide the widget if click is outside
           * @private
           * @param e MouseEvent
           */
          this._documentClickEvent = (e) => {
              if (this.optionsStore.options.debug || window.debug)
                  return; //eslint-disable-line @typescript-eslint/no-explicit-any
              if (this._isVisible &&
                  !e.composedPath().includes(this.widget) && // click inside the widget
                  !e.composedPath()?.includes(this.optionsStore.element) // click on the element
              ) {
                  this.hide();
              }
          };
          /**
           * Click event for any action like selecting a date
           * @param e MouseEvent
           * @private
           */
          this._actionsClickEvent = (e) => {
              this._eventEmitters.action.emit({ e: e });
          };
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.validation = serviceLocator.locate(Validation);
          this.dates = serviceLocator.locate(Dates);
          this.dateDisplay = serviceLocator.locate(DateDisplay);
          this.monthDisplay = serviceLocator.locate(MonthDisplay);
          this.yearDisplay = serviceLocator.locate(YearDisplay);
          this.decadeDisplay = serviceLocator.locate(DecadeDisplay);
          this.timeDisplay = serviceLocator.locate(TimeDisplay);
          this.hourDisplay = serviceLocator.locate(HourDisplay);
          this.minuteDisplay = serviceLocator.locate(MinuteDisplay);
          this.secondDisplay = serviceLocator.locate(secondDisplay);
          this._eventEmitters = serviceLocator.locate(EventEmitters);
          this._widget = undefined;
          this._eventEmitters.updateDisplay.subscribe((result) => {
              this._update(result);
          });
      }
      /**
       * Returns the widget body or undefined
       * @private
       */
      get widget() {
          return this._widget;
      }
      get dateContainer() {
          return this.widget?.querySelector(`div.${Namespace.css.dateContainer}`);
      }
      get timeContainer() {
          return this.widget?.querySelector(`div.${Namespace.css.timeContainer}`);
      }
      /**
       * Returns this visible state of the picker (shown)
       */
      get isVisible() {
          return this._isVisible;
      }
      /**
       * Updates the table for a particular unit. Used when an option as changed or
       * whenever the class list might need to be refreshed.
       * @param unit
       * @private
       */
      _update(unit) {
          if (!this.widget)
              return;
          switch (unit) {
              case exports.Unit.seconds:
                  this.secondDisplay._update(this.widget, this.paint);
                  break;
              case exports.Unit.minutes:
                  this.minuteDisplay._update(this.widget, this.paint);
                  break;
              case exports.Unit.hours:
                  this.hourDisplay._update(this.widget, this.paint);
                  break;
              case exports.Unit.date:
                  this.dateDisplay._update(this.widget, this.paint);
                  break;
              case exports.Unit.month:
                  this.monthDisplay._update(this.widget, this.paint);
                  break;
              case exports.Unit.year:
                  this.yearDisplay._update(this.widget, this.paint);
                  break;
              case 'decade':
                  this.decadeDisplay._update(this.widget, this.paint);
                  break;
              case 'clock':
                  if (!this._hasTime)
                      break;
                  this.timeDisplay._update(this.widget);
                  this._update(exports.Unit.hours);
                  this._update(exports.Unit.minutes);
                  this._update(exports.Unit.seconds);
                  break;
              case 'calendar':
                  this._update(exports.Unit.date);
                  this._update(exports.Unit.year);
                  this._update(exports.Unit.month);
                  this.decadeDisplay._update(this.widget, this.paint);
                  this._updateCalendarHeader();
                  break;
              case 'all':
                  if (this._hasTime) {
                      this._update('clock');
                  }
                  if (this._hasDate) {
                      this._update('calendar');
                  }
          }
      }
      // noinspection JSUnusedLocalSymbols
      /**
       * Allows developers to add/remove classes from an element.
       * @param _unit
       * @param _date
       * @param _classes
       * @param _element
       */
      /* eslint-disable @typescript-eslint/no-unused-vars */
      paint(_unit, _date, _classes, _element) {
          // implemented in plugin
      }
      /**
       * Shows the picker and creates a Popper instance if needed.
       * Add document click event to hide when clicking outside the picker.
       * fires Events#show
       */
      show() {
          if (this.widget == undefined) {
              this._showSetDefaultIfNeeded();
              this._buildWidget();
              this._updateTheme();
              this._showSetupViewMode();
              if (!this.optionsStore.options.display.inline) {
                  // If needed to change the parent container
                  const container = this.optionsStore.options?.container || document.body;
                  const placement = this.optionsStore.options?.display?.placement || 'bottom';
                  container.appendChild(this.widget);
                  this.createPopup(this.optionsStore.element, this.widget, {
                      modifiers: [{ name: 'eventListeners', enabled: true }],
                      //#2400
                      placement: document.documentElement.dir === 'rtl'
                          ? `${placement}-end`
                          : `${placement}-start`,
                  }).then();
              }
              else {
                  this.optionsStore.element.appendChild(this.widget);
              }
              if (this.optionsStore.options.display.viewMode == 'clock') {
                  this._eventEmitters.action.emit({
                      e: null,
                      action: ActionTypes$1.showClock,
                  });
              }
              this.widget
                  .querySelectorAll('[data-action]')
                  .forEach((element) => element.addEventListener('click', this._actionsClickEvent));
              // show the clock when using sideBySide
              if (this._hasTime && this.optionsStore.options.display.sideBySide) {
                  this.timeDisplay._update(this.widget);
                  this.widget.getElementsByClassName(Namespace.css.clockContainer)[0].style.display = 'grid';
              }
          }
          this.widget.classList.add(Namespace.css.show);
          if (!this.optionsStore.options.display.inline) {
              this.updatePopup();
              document.addEventListener('click', this._documentClickEvent);
          }
          this._eventEmitters.triggerEvent.emit({ type: Namespace.events.show });
          this._isVisible = true;
      }
      _showSetupViewMode() {
          // If modeView is only clock
          const onlyClock = this._hasTime && !this._hasDate;
          // reset the view to the clock if there's no date components
          if (onlyClock) {
              this.optionsStore.currentView = 'clock';
              this._eventEmitters.action.emit({
                  e: null,
                  action: ActionTypes$1.showClock,
              });
          }
          // otherwise return to the calendar view
          else if (!this.optionsStore.currentCalendarViewMode) {
              this.optionsStore.currentCalendarViewMode =
                  this.optionsStore.minimumCalendarViewMode;
          }
          if (!onlyClock && this.optionsStore.options.display.viewMode !== 'clock') {
              if (this._hasTime) {
                  if (!this.optionsStore.options.display.sideBySide) {
                      Collapse.hideImmediately(this.timeContainer);
                  }
                  else {
                      Collapse.show(this.timeContainer);
                  }
              }
              Collapse.show(this.dateContainer);
          }
          if (this._hasDate) {
              this._showMode();
          }
      }
      _showSetDefaultIfNeeded() {
          if (this.dates.picked.length != 0)
              return;
          if (this.optionsStore.options.useCurrent &&
              !this.optionsStore.options.defaultDate) {
              const date = new DateTime().setLocalization(this.optionsStore.options.localization);
              if (!this.optionsStore.options.keepInvalid) {
                  let tries = 0;
                  let direction = 1;
                  if (this.optionsStore.options.restrictions.maxDate?.isBefore(date)) {
                      direction = -1;
                  }
                  while (!this.validation.isValid(date) && tries > 31) {
                      date.manipulate(direction, exports.Unit.date);
                      tries++;
                  }
              }
              this.dates.setValue(date);
          }
          if (this.optionsStore.options.defaultDate) {
              this.dates.setValue(this.optionsStore.options.defaultDate);
          }
      }
      async createPopup(element, widget, 
      //eslint-disable-next-line @typescript-eslint/no-explicit-any
      options) {
          let createPopperFunction;
          //eslint-disable-next-line @typescript-eslint/no-explicit-any
          if (window?.Popper) {
              //eslint-disable-next-line @typescript-eslint/no-explicit-any
              createPopperFunction = window?.Popper?.createPopper;
          }
          else {
              const { createPopper } = await import('@popperjs/core');
              createPopperFunction = createPopper;
          }
          if (createPopperFunction) {
              this._popperInstance = createPopperFunction(element, widget, options);
          }
      }
      updatePopup() {
          this._popperInstance?.update();
      }
      /**
       * Changes the calendar view mode. E.g. month <-> year
       * @param direction -/+ number to move currentViewMode
       * @private
       */
      _showMode(direction) {
          if (!this.widget) {
              return;
          }
          if (direction) {
              const max = Math.max(this.optionsStore.minimumCalendarViewMode, Math.min(3, this.optionsStore.currentCalendarViewMode + direction));
              if (this.optionsStore.currentCalendarViewMode == max)
                  return;
              this.optionsStore.currentCalendarViewMode = max;
          }
          this.widget
              .querySelectorAll(`.${Namespace.css.dateContainer} > div:not(.${Namespace.css.calendarHeader}), .${Namespace.css.timeContainer} > div:not(.${Namespace.css.clockContainer})`)
              .forEach((e) => (e.style.display = 'none'));
          const datePickerMode = CalendarModes[this.optionsStore.currentCalendarViewMode];
          const picker = this.widget.querySelector(`.${datePickerMode.className}`);
          switch (datePickerMode.className) {
              case Namespace.css.decadesContainer:
                  this.decadeDisplay._update(this.widget, this.paint);
                  break;
              case Namespace.css.yearsContainer:
                  this.yearDisplay._update(this.widget, this.paint);
                  break;
              case Namespace.css.monthsContainer:
                  this.monthDisplay._update(this.widget, this.paint);
                  break;
              case Namespace.css.daysContainer:
                  this.dateDisplay._update(this.widget, this.paint);
                  break;
          }
          picker.style.display = 'grid';
          if (this.optionsStore.options.display.sideBySide)
              (this.widget.querySelectorAll(`.${Namespace.css.clockContainer}`)[0]).style.display = 'grid';
          this._updateCalendarHeader();
          this._eventEmitters.viewUpdate.emit();
      }
      /**
       * Changes the theme. E.g. light, dark or auto
       * @param theme the theme name
       * @private
       */
      _updateTheme(theme) {
          if (!this.widget) {
              return;
          }
          if (theme) {
              if (this.optionsStore.options.display.theme === theme)
                  return;
              this.optionsStore.options.display.theme = theme;
          }
          this.widget.classList.remove('light', 'dark');
          this.widget.classList.add(this._getThemeClass());
          if (this.optionsStore.options.display.theme === 'auto') {
              window
                  .matchMedia(Namespace.css.isDarkPreferredQuery)
                  .addEventListener('change', () => this._updateTheme());
          }
          else {
              window
                  .matchMedia(Namespace.css.isDarkPreferredQuery)
                  .removeEventListener('change', () => this._updateTheme());
          }
      }
      _getThemeClass() {
          const currentTheme = this.optionsStore.options.display.theme || 'auto';
          const isDarkMode = window.matchMedia &&
              window.matchMedia(Namespace.css.isDarkPreferredQuery).matches;
          switch (currentTheme) {
              case 'light':
                  return Namespace.css.lightTheme;
              case 'dark':
                  return Namespace.css.darkTheme;
              case 'auto':
                  return isDarkMode ? Namespace.css.darkTheme : Namespace.css.lightTheme;
          }
      }
      _updateCalendarHeader() {
          if (!this._hasDate)
              return;
          const showing = [
              ...this.widget.querySelector(`.${Namespace.css.dateContainer} div[style*="display: grid"]`).classList,
          ].find((x) => x.startsWith(Namespace.css.dateContainer));
          const [previous, switcher, next] = this.widget
              .getElementsByClassName(Namespace.css.calendarHeader)[0]
              .getElementsByTagName('div');
          switch (showing) {
              case Namespace.css.decadesContainer:
                  previous.setAttribute('title', this.optionsStore.options.localization.previousCentury);
                  switcher.setAttribute('title', '');
                  next.setAttribute('title', this.optionsStore.options.localization.nextCentury);
                  break;
              case Namespace.css.yearsContainer:
                  previous.setAttribute('title', this.optionsStore.options.localization.previousDecade);
                  switcher.setAttribute('title', this.optionsStore.options.localization.selectDecade);
                  next.setAttribute('title', this.optionsStore.options.localization.nextDecade);
                  break;
              case Namespace.css.monthsContainer:
                  previous.setAttribute('title', this.optionsStore.options.localization.previousYear);
                  switcher.setAttribute('title', this.optionsStore.options.localization.selectYear);
                  next.setAttribute('title', this.optionsStore.options.localization.nextYear);
                  break;
              case Namespace.css.daysContainer:
                  previous.setAttribute('title', this.optionsStore.options.localization.previousMonth);
                  switcher.setAttribute('title', this.optionsStore.options.localization.selectMonth);
                  next.setAttribute('title', this.optionsStore.options.localization.nextMonth);
                  switcher.setAttribute(showing, this.optionsStore.viewDate.format(this.optionsStore.options.localization.dayViewHeaderFormat));
                  break;
          }
          switcher.innerText = switcher.getAttribute(showing);
      }
      /**
       * Hides the picker if needed.
       * Remove document click event to hide when clicking outside the picker.
       * fires Events#hide
       */
      hide() {
          if (!this.widget || !this._isVisible)
              return;
          this.widget.classList.remove(Namespace.css.show);
          if (this._isVisible) {
              this._eventEmitters.triggerEvent.emit({
                  type: Namespace.events.hide,
                  date: this.optionsStore.unset ? null : this.dates.lastPicked?.clone,
              });
              this._isVisible = false;
          }
          document.removeEventListener('click', this._documentClickEvent);
      }
      /**
       * Toggles the picker's open state. Fires a show/hide event depending.
       */
      toggle() {
          return this._isVisible ? this.hide() : this.show();
      }
      /**
       * Removes document and data-action click listener and reset the widget
       * @private
       */
      _dispose() {
          document.removeEventListener('click', this._documentClickEvent);
          if (!this.widget)
              return;
          this.widget
              .querySelectorAll('[data-action]')
              .forEach((element) => element.removeEventListener('click', this._actionsClickEvent));
          this.widget.parentNode.removeChild(this.widget);
          this._widget = undefined;
      }
      /**
       * Builds the widgets html template.
       * @private
       */
      _buildWidget() {
          const template = document.createElement('div');
          template.classList.add(Namespace.css.widget);
          const dateView = document.createElement('div');
          dateView.classList.add(Namespace.css.dateContainer);
          dateView.append(this.getHeadTemplate(), this.decadeDisplay.getPicker(), this.yearDisplay.getPicker(), this.monthDisplay.getPicker(), this.dateDisplay.getPicker());
          const timeView = document.createElement('div');
          timeView.classList.add(Namespace.css.timeContainer);
          timeView.appendChild(this.timeDisplay.getPicker(this._iconTag.bind(this)));
          timeView.appendChild(this.hourDisplay.getPicker());
          timeView.appendChild(this.minuteDisplay.getPicker());
          timeView.appendChild(this.secondDisplay.getPicker());
          const toolbar = document.createElement('div');
          toolbar.classList.add(Namespace.css.toolbar);
          toolbar.append(...this.getToolbarElements());
          if (this.optionsStore.options.display.inline) {
              template.classList.add(Namespace.css.inline);
          }
          if (this.optionsStore.options.display.calendarWeeks) {
              template.classList.add('calendarWeeks');
          }
          if (this.optionsStore.options.display.sideBySide && this._hasDateAndTime) {
              this._buildWidgetSideBySide(template, dateView, timeView, toolbar);
              return;
          }
          if (this.optionsStore.options.display.toolbarPlacement === 'top') {
              template.appendChild(toolbar);
          }
          const setupComponentView = (hasFirst, hasSecond, element, shouldShow) => {
              if (!hasFirst)
                  return;
              if (hasSecond) {
                  element.classList.add(Namespace.css.collapse);
                  if (shouldShow)
                      element.classList.add(Namespace.css.show);
              }
              template.appendChild(element);
          };
          setupComponentView(this._hasDate, this._hasTime, dateView, this.optionsStore.options.display.viewMode !== 'clock');
          setupComponentView(this._hasTime, this._hasDate, timeView, this.optionsStore.options.display.viewMode === 'clock');
          if (this.optionsStore.options.display.toolbarPlacement === 'bottom') {
              template.appendChild(toolbar);
          }
          const arrow = document.createElement('div');
          arrow.classList.add('arrow');
          arrow.setAttribute('data-popper-arrow', '');
          template.appendChild(arrow);
          this._widget = template;
      }
      _buildWidgetSideBySide(template, dateView, timeView, toolbar) {
          template.classList.add(Namespace.css.sideBySide);
          if (this.optionsStore.options.display.toolbarPlacement === 'top') {
              template.appendChild(toolbar);
          }
          const row = document.createElement('div');
          row.classList.add('td-row');
          dateView.classList.add('td-half');
          timeView.classList.add('td-half');
          row.appendChild(dateView);
          row.appendChild(timeView);
          template.appendChild(row);
          if (this.optionsStore.options.display.toolbarPlacement === 'bottom') {
              template.appendChild(toolbar);
          }
          this._widget = template;
      }
      /**
       * Returns true if the hours, minutes, or seconds component is turned on
       */
      get _hasTime() {
          return (this.optionsStore.options.display.components.clock &&
              (this.optionsStore.options.display.components.hours ||
                  this.optionsStore.options.display.components.minutes ||
                  this.optionsStore.options.display.components.seconds));
      }
      /**
       * Returns true if the year, month, or date component is turned on
       */
      get _hasDate() {
          return (this.optionsStore.options.display.components.calendar &&
              (this.optionsStore.options.display.components.year ||
                  this.optionsStore.options.display.components.month ||
                  this.optionsStore.options.display.components.date));
      }
      get _hasDateAndTime() {
          return this._hasDate && this._hasTime;
      }
      /**
       * Get the toolbar html based on options like buttons => today
       * @private
       */
      getToolbarElements() {
          const toolbar = [];
          if (this.optionsStore.options.display.buttons.today) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.today);
              div.setAttribute('title', this.optionsStore.options.localization.today);
              div.appendChild(this._iconTag(this.optionsStore.options.display.icons.today));
              toolbar.push(div);
          }
          if (!this.optionsStore.options.display.sideBySide &&
              this._hasDate &&
              this._hasTime) {
              let title, icon;
              if (this.optionsStore.options.display.viewMode === 'clock') {
                  title = this.optionsStore.options.localization.selectDate;
                  icon = this.optionsStore.options.display.icons.date;
              }
              else {
                  title = this.optionsStore.options.localization.selectTime;
                  icon = this.optionsStore.options.display.icons.time;
              }
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.togglePicker);
              div.setAttribute('title', title);
              div.appendChild(this._iconTag(icon));
              toolbar.push(div);
          }
          if (this.optionsStore.options.display.buttons.clear) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.clear);
              div.setAttribute('title', this.optionsStore.options.localization.clear);
              div.appendChild(this._iconTag(this.optionsStore.options.display.icons.clear));
              toolbar.push(div);
          }
          if (this.optionsStore.options.display.buttons.close) {
              const div = document.createElement('div');
              div.setAttribute('data-action', ActionTypes$1.close);
              div.setAttribute('title', this.optionsStore.options.localization.close);
              div.appendChild(this._iconTag(this.optionsStore.options.display.icons.close));
              toolbar.push(div);
          }
          return toolbar;
      }
      /***
       * Builds the base header template with next and previous icons
       * @private
       */
      getHeadTemplate() {
          const calendarHeader = document.createElement('div');
          calendarHeader.classList.add(Namespace.css.calendarHeader);
          const previous = document.createElement('div');
          previous.classList.add(Namespace.css.previous);
          previous.setAttribute('data-action', ActionTypes$1.previous);
          previous.appendChild(this._iconTag(this.optionsStore.options.display.icons.previous));
          const switcher = document.createElement('div');
          switcher.classList.add(Namespace.css.switch);
          switcher.setAttribute('data-action', ActionTypes$1.changeCalendarView);
          const next = document.createElement('div');
          next.classList.add(Namespace.css.next);
          next.setAttribute('data-action', ActionTypes$1.next);
          next.appendChild(this._iconTag(this.optionsStore.options.display.icons.next));
          calendarHeader.append(previous, switcher, next);
          return calendarHeader;
      }
      /**
       * Builds an icon tag as either an `<i>`
       * or with icons => type is `sprites` then a svg tag instead
       * @param iconClass
       * @private
       */
      _iconTag(iconClass) {
          if (this.optionsStore.options.display.icons.type === 'sprites') {
              const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
              const icon = document.createElementNS('http://www.w3.org/2000/svg', 'use');
              icon.setAttribute('xlink:href', iconClass); // Deprecated. Included for backward compatibility
              icon.setAttribute('href', iconClass);
              svg.appendChild(icon);
              return svg;
          }
          const icon = document.createElement('i');
          icon.classList.add(...iconClass.split(' '));
          return icon;
      }
      /**
       * Causes the widget to get rebuilt on next show. If the picker is already open
       * then hide and reshow it.
       * @private
       */
      _rebuild() {
          const wasVisible = this._isVisible;
          this._dispose();
          if (wasVisible)
              this.show();
      }
      refreshCurrentView() {
          //if the widget is not showing, just destroy it
          if (!this._isVisible)
              this._dispose();
          switch (this.optionsStore.currentView) {
              case 'clock':
                  this._update('clock');
                  break;
              case 'calendar':
                  this._update(exports.Unit.date);
                  break;
              case 'months':
                  this._update(exports.Unit.month);
                  break;
              case 'years':
                  this._update(exports.Unit.year);
                  break;
              case 'decades':
                  this._update('decade');
                  break;
          }
      }
  }

  /**
   * Logic for various click actions
   */
  class Actions {
      constructor() {
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.dates = serviceLocator.locate(Dates);
          this.validation = serviceLocator.locate(Validation);
          this.display = serviceLocator.locate(Display);
          this._eventEmitters = serviceLocator.locate(EventEmitters);
          this._eventEmitters.action.subscribe((result) => {
              this.do(result.e, result.action);
          });
      }
      /**
       * Performs the selected `action`. See ActionTypes
       * @param e This is normally a click event
       * @param action If not provided, then look for a [data-action]
       */
      //eslint-disable-next-line @typescript-eslint/no-explicit-any
      do(e, action) {
          const currentTarget = e?.currentTarget;
          if (currentTarget?.classList?.contains(Namespace.css.disabled))
              return;
          action = action || currentTarget?.dataset?.action;
          const lastPicked = (this.dates.lastPicked || this.optionsStore.viewDate)
              .clone;
          switch (action) {
              case ActionTypes$1.next:
              case ActionTypes$1.previous:
                  this.handleNextPrevious(action);
                  break;
              case ActionTypes$1.changeCalendarView:
                  this.display._showMode(1);
                  this.display._updateCalendarHeader();
                  break;
              case ActionTypes$1.selectMonth:
              case ActionTypes$1.selectYear:
              case ActionTypes$1.selectDecade:
                  this.handleSelectCalendarMode(action, currentTarget);
                  break;
              case ActionTypes$1.selectDay:
                  this.handleSelectDay(currentTarget);
                  break;
              case ActionTypes$1.selectHour: {
                  let hour = +currentTarget.dataset.value;
                  if (lastPicked.hours >= 12 && this.optionsStore.isTwelveHour)
                      hour += 12;
                  lastPicked.hours = hour;
                  this.dates.setValue(lastPicked, this.dates.lastPickedIndex);
                  this.hideOrClock(e);
                  break;
              }
              case ActionTypes$1.selectMinute: {
                  lastPicked.minutes = +currentTarget.dataset.value;
                  this.dates.setValue(lastPicked, this.dates.lastPickedIndex);
                  this.hideOrClock(e);
                  break;
              }
              case ActionTypes$1.selectSecond: {
                  lastPicked.seconds = +currentTarget.dataset.value;
                  this.dates.setValue(lastPicked, this.dates.lastPickedIndex);
                  this.hideOrClock(e);
                  break;
              }
              case ActionTypes$1.incrementHours:
                  this.manipulateAndSet(lastPicked, exports.Unit.hours);
                  break;
              case ActionTypes$1.incrementMinutes:
                  this.manipulateAndSet(lastPicked, exports.Unit.minutes, this.optionsStore.options.stepping);
                  break;
              case ActionTypes$1.incrementSeconds:
                  this.manipulateAndSet(lastPicked, exports.Unit.seconds);
                  break;
              case ActionTypes$1.decrementHours:
                  this.manipulateAndSet(lastPicked, exports.Unit.hours, -1);
                  break;
              case ActionTypes$1.decrementMinutes:
                  this.manipulateAndSet(lastPicked, exports.Unit.minutes, this.optionsStore.options.stepping * -1);
                  break;
              case ActionTypes$1.decrementSeconds:
                  this.manipulateAndSet(lastPicked, exports.Unit.seconds, -1);
                  break;
              case ActionTypes$1.toggleMeridiem:
                  this.manipulateAndSet(lastPicked, exports.Unit.hours, this.dates.lastPicked.hours >= 12 ? -12 : 12);
                  break;
              case ActionTypes$1.togglePicker:
                  this.handleToggle(currentTarget);
                  break;
              case ActionTypes$1.showClock:
              case ActionTypes$1.showHours:
              case ActionTypes$1.showMinutes:
              case ActionTypes$1.showSeconds:
                  //make sure the clock is actually displaying
                  if (!this.optionsStore.options.display.sideBySide &&
                      this.optionsStore.currentView !== 'clock') {
                      //hide calendar
                      Collapse.hideImmediately(this.display.dateContainer);
                      //show clock
                      Collapse.showImmediately(this.display.timeContainer);
                  }
                  this.handleShowClockContainers(action);
                  break;
              case ActionTypes$1.clear:
                  this.dates.setValue(null);
                  this.display._updateCalendarHeader();
                  break;
              case ActionTypes$1.close:
                  this.display.hide();
                  break;
              case ActionTypes$1.today: {
                  const day = new DateTime().setLocalization(this.optionsStore.options.localization);
                  this._eventEmitters.updateViewDate.emit(day);
                  if (!this.validation.isValid(day, exports.Unit.date))
                      break;
                  if (this.optionsStore.options.dateRange)
                      this.handleDateRange(day);
                  else if (this.optionsStore.options.multipleDates) {
                      this.handleMultiDate(day);
                  }
                  else {
                      this.dates.setValue(day, this.dates.lastPickedIndex);
                  }
                  break;
              }
          }
      }
      handleShowClockContainers(action) {
          if (!this.display._hasTime) {
              Namespace.errorMessages.throwError('Cannot show clock containers when time is disabled.');
              /* ignore coverage: should never happen */
              return;
          }
          this.optionsStore.currentView = 'clock';
          this.display.widget
              .querySelectorAll(`.${Namespace.css.timeContainer} > div`)
              .forEach((htmlElement) => (htmlElement.style.display = 'none'));
          let classToUse = '';
          switch (action) {
              case ActionTypes$1.showClock:
                  classToUse = Namespace.css.clockContainer;
                  this.display._update('clock');
                  break;
              case ActionTypes$1.showHours:
                  classToUse = Namespace.css.hourContainer;
                  this.display._update(exports.Unit.hours);
                  break;
              case ActionTypes$1.showMinutes:
                  classToUse = Namespace.css.minuteContainer;
                  this.display._update(exports.Unit.minutes);
                  break;
              case ActionTypes$1.showSeconds:
                  classToUse = Namespace.css.secondContainer;
                  this.display._update(exports.Unit.seconds);
                  break;
          }
          (this.display.widget.getElementsByClassName(classToUse)[0]).style.display = 'grid';
      }
      handleNextPrevious(action) {
          const { unit, step } = CalendarModes[this.optionsStore.currentCalendarViewMode];
          if (action === ActionTypes$1.next)
              this.optionsStore.viewDate.manipulate(step, unit);
          else
              this.optionsStore.viewDate.manipulate(step * -1, unit);
          this._eventEmitters.viewUpdate.emit();
          this.display._showMode();
      }
      /**
       * After setting the value it will either show the clock or hide the widget.
       * @param e
       */
      hideOrClock(e) {
          if (!this.optionsStore.isTwelveHour &&
              !this.optionsStore.options.display.components.minutes &&
              !this.optionsStore.options.display.keepOpen &&
              !this.optionsStore.options.display.inline) {
              this.display.hide();
          }
          else {
              this.do(e, ActionTypes$1.showClock);
          }
      }
      /**
       * Common function to manipulate {@link lastPicked} by `unit`.
       * @param lastPicked
       * @param unit
       * @param value Value to change by
       */
      manipulateAndSet(lastPicked, unit, value = 1) {
          const newDate = lastPicked.manipulate(value, unit);
          if (this.validation.isValid(newDate, unit)) {
              this.dates.setValue(newDate, this.dates.lastPickedIndex);
          }
      }
      handleSelectCalendarMode(action, currentTarget) {
          const value = +currentTarget.dataset.value;
          switch (action) {
              case ActionTypes$1.selectMonth:
                  this.optionsStore.viewDate.month = value;
                  break;
              case ActionTypes$1.selectYear:
              case ActionTypes$1.selectDecade:
                  this.optionsStore.viewDate.year = value;
                  break;
          }
          if (this.optionsStore.currentCalendarViewMode ===
              this.optionsStore.minimumCalendarViewMode) {
              this.dates.setValue(this.optionsStore.viewDate, this.dates.lastPickedIndex);
              if (!this.optionsStore.options.display.inline) {
                  this.display.hide();
              }
          }
          else {
              this.display._showMode(-1);
          }
      }
      handleToggle(currentTarget) {
          if (currentTarget.getAttribute('title') ===
              this.optionsStore.options.localization.selectDate) {
              currentTarget.setAttribute('title', this.optionsStore.options.localization.selectTime);
              currentTarget.innerHTML = this.display._iconTag(this.optionsStore.options.display.icons.time).outerHTML;
              this.display._updateCalendarHeader();
              this.optionsStore.refreshCurrentView();
          }
          else {
              currentTarget.setAttribute('title', this.optionsStore.options.localization.selectDate);
              currentTarget.innerHTML = this.display._iconTag(this.optionsStore.options.display.icons.date).outerHTML;
              if (this.display._hasTime) {
                  this.handleShowClockContainers(ActionTypes$1.showClock);
                  this.display._update('clock');
              }
          }
          this.display.widget
              .querySelectorAll(`.${Namespace.css.dateContainer}, .${Namespace.css.timeContainer}`)
              .forEach((htmlElement) => Collapse.toggle(htmlElement));
          this._eventEmitters.viewUpdate.emit();
      }
      handleSelectDay(currentTarget) {
          const day = this.optionsStore.viewDate.clone;
          if (currentTarget.classList.contains(Namespace.css.old)) {
              day.manipulate(-1, exports.Unit.month);
          }
          if (currentTarget.classList.contains(Namespace.css.new)) {
              day.manipulate(1, exports.Unit.month);
          }
          day.date = +currentTarget.dataset.day;
          if (this.optionsStore.options.dateRange)
              this.handleDateRange(day);
          else if (this.optionsStore.options.multipleDates) {
              this.handleMultiDate(day);
          }
          else {
              this.dates.setValue(day, this.dates.lastPickedIndex);
          }
          if (!this.display._hasTime &&
              !this.optionsStore.options.display.keepOpen &&
              !this.optionsStore.options.display.inline &&
              !this.optionsStore.options.multipleDates &&
              !this.optionsStore.options.dateRange) {
              this.display.hide();
          }
      }
      handleMultiDate(day) {
          let index = this.dates.pickedIndex(day, exports.Unit.date);
          console.log(index);
          if (index !== -1) {
              this.dates.setValue(null, index); //deselect multi-date
          }
          else {
              index = this.dates.lastPickedIndex + 1;
              if (this.dates.picked.length === 0)
                  index = 0;
              this.dates.setValue(day, index);
          }
      }
      handleDateRange(day) {
          switch (this.dates.picked.length) {
              case 2: {
                  this.dates.clear();
                  break;
              }
              case 1: {
                  const other = this.dates.picked[0];
                  if (day.getTime() === other.getTime()) {
                      this.dates.clear();
                      break;
                  }
                  if (day.isBefore(other)) {
                      this.dates.setValue(day, 0);
                      this.dates.setValue(other, 1);
                      return;
                  }
                  else {
                      this.dates.setValue(day, 1);
                      return;
                  }
              }
          }
          this.dates.setValue(day, 0);
      }
  }

  /**
   * A robust and powerful date/time picker component.
   */
  class TempusDominus {
      constructor(element, options = {}) {
          //eslint-disable-next-line @typescript-eslint/no-explicit-any
          this._subscribers = {};
          this._isDisabled = false;
          /**
           * Event for when the input field changes. This is a class level method so there's
           * something for the remove listener function.
           * @private
           */
          //eslint-disable-next-line @typescript-eslint/no-explicit-any
          this._inputChangeEvent = (event) => {
              const internallyTriggered = event?.detail;
              if (internallyTriggered)
                  return;
              const setViewDate = () => {
                  if (this.dates.lastPicked)
                      this.optionsStore.viewDate = this.dates.lastPicked.clone;
              };
              const value = this.optionsStore.input.value;
              if (this.optionsStore.options.multipleDates ||
                  this.optionsStore.options.dateRange) {
                  try {
                      const valueSplit = value.split(this.optionsStore.options.multipleDatesSeparator);
                      for (let i = 0; i < valueSplit.length; i++) {
                          this.dates.setFromInput(valueSplit[i], i);
                      }
                      setViewDate();
                  }
                  catch {
                      console.warn('TD: Something went wrong trying to set the multipleDates values from the input field.');
                  }
              }
              else {
                  this.dates.setFromInput(value, 0);
                  setViewDate();
              }
          };
          /**
           * Event for when the toggle is clicked. This is a class level method so there's
           * something for the remove listener function.
           * @private
           */
          this._toggleClickEvent = () => {
              if (this.optionsStore.element?.disabled ||
                  this.optionsStore.input?.disabled ||
                  //if we just have the input and allow input toggle is enabled, then don't cause a toggle
                  (this._toggle.nodeName === 'INPUT' &&
                      this._toggle?.type === 'text' &&
                      this.optionsStore.options.allowInputToggle))
                  return;
              this.toggle();
          };
          /**
           * Event for when the toggle is clicked. This is a class level method so there's
           * something for the remove listener function.
           * @private
           */
          this._openClickEvent = () => {
              if (this.optionsStore.element?.disabled ||
                  this.optionsStore.input?.disabled)
                  return;
              if (!this.display.isVisible)
                  this.show();
          };
          setupServiceLocator();
          this._eventEmitters = serviceLocator.locate(EventEmitters);
          this.optionsStore = serviceLocator.locate(OptionsStore);
          this.display = serviceLocator.locate(Display);
          this.dates = serviceLocator.locate(Dates);
          this.actions = serviceLocator.locate(Actions);
          if (!element) {
              Namespace.errorMessages.mustProvideElement();
          }
          this.optionsStore.element = element;
          this._initializeOptions(options, DefaultOptions, true);
          this.optionsStore.viewDate.setLocalization(this.optionsStore.options.localization);
          this.optionsStore.unset = true;
          this._initializeInput();
          this._initializeToggle();
          if (this.optionsStore.options.display.inline)
              this.display.show();
          this._eventEmitters.triggerEvent.subscribe((e) => {
              this._triggerEvent(e);
          });
          this._eventEmitters.viewUpdate.subscribe(() => {
              this._viewUpdate();
          });
          this._eventEmitters.updateViewDate.subscribe((dateTime) => {
              this.viewDate = dateTime;
          });
      }
      get viewDate() {
          return this.optionsStore.viewDate;
      }
      set viewDate(value) {
          this.optionsStore.viewDate = value;
          this.optionsStore.viewDate.setLocalization(this.optionsStore.options.localization);
          this.display._update(this.optionsStore.currentView === 'clock' ? 'clock' : 'calendar');
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Update the picker options. If `reset` is provide `options` will be merged with DefaultOptions instead.
       * @param options
       * @param reset
       * @public
       */
      updateOptions(options, reset = false) {
          if (reset)
              this._initializeOptions(options, DefaultOptions);
          else
              this._initializeOptions(options, this.optionsStore.options);
          this.optionsStore.viewDate.setLocalization(this.optionsStore.options.localization);
          this.display.refreshCurrentView();
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Toggles the picker open or closed. If the picker is disabled, nothing will happen.
       * @public
       */
      toggle() {
          if (this._isDisabled)
              return;
          this.display.toggle();
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Shows the picker unless the picker is disabled.
       * @public
       */
      show() {
          if (this._isDisabled)
              return;
          this.display.show();
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Hides the picker unless the picker is disabled.
       * @public
       */
      hide() {
          this.display.hide();
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Disables the picker and the target input field.
       * @public
       */
      disable() {
          this._isDisabled = true;
          // todo this might be undesired. If a dev disables the input field to
          // only allow using the picker, this will break that.
          this.optionsStore.input?.setAttribute('disabled', 'disabled');
          this.display.hide();
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Enables the picker and the target input field.
       * @public
       */
      enable() {
          this._isDisabled = false;
          this.optionsStore.input?.removeAttribute('disabled');
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Clears all the selected dates
       * @public
       */
      clear() {
          this.optionsStore.input.value = '';
          this.dates.clear();
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Allows for a direct subscription to picker events, without having to use addEventListener on the element.
       * @param eventTypes See Namespace.Events
       * @param callbacks Function to call when event is triggered
       * @public
       */
      subscribe(eventTypes, callbacks //eslint-disable-line @typescript-eslint/no-explicit-any
      ) {
          if (typeof eventTypes === 'string') {
              eventTypes = [eventTypes];
          }
          let callBackArray; //eslint-disable-line @typescript-eslint/no-explicit-any
          if (!Array.isArray(callbacks)) {
              callBackArray = [callbacks];
          }
          else {
              callBackArray = callbacks;
          }
          if (eventTypes.length !== callBackArray.length) {
              Namespace.errorMessages.subscribeMismatch();
          }
          const returnArray = [];
          for (let i = 0; i < eventTypes.length; i++) {
              const eventType = eventTypes[i];
              if (!Array.isArray(this._subscribers[eventType])) {
                  this._subscribers[eventType] = [];
              }
              this._subscribers[eventType].push(callBackArray[i]);
              returnArray.push({
                  unsubscribe: this._unsubscribe.bind(this, eventType, this._subscribers[eventType].length - 1),
              });
              if (eventTypes.length === 1) {
                  return returnArray[0];
              }
          }
          return returnArray;
      }
      // noinspection JSUnusedGlobalSymbols
      /**
       * Hides the picker and removes event listeners
       */
      dispose() {
          this.display.hide();
          // this will clear the document click event listener
          this.display._dispose();
          this._eventEmitters.destroy();
          this.optionsStore.input?.removeEventListener('change', this._inputChangeEvent);
          if (this.optionsStore.options.allowInputToggle) {
              this.optionsStore.input?.removeEventListener('click', this._openClickEvent);
              this.optionsStore.input?.removeEventListener('focus', this._openClickEvent);
          }
          this._toggle?.removeEventListener('click', this._toggleClickEvent);
          this._subscribers = {};
      }
      /**
       * Updates the options to use the provided language.
       * THe language file must be loaded first.
       * @param language
       */
      locale(language) {
          const asked = loadedLocales[language];
          if (!asked)
              return;
          this.updateOptions({
              localization: asked,
          });
      }
      /**
       * Triggers an event like ChangeEvent when the picker has updated the value
       * of a selected date.
       * @param event Accepts a BaseEvent object.
       * @private
       */
      _triggerEvent(event) {
          event.viewMode = this.optionsStore.currentView;
          const isChangeEvent = event.type === Namespace.events.change;
          if (isChangeEvent) {
              const { date, oldDate, isClear } = event;
              if ((date && oldDate && date.isSame(oldDate)) ||
                  (!isClear && !date && !oldDate)) {
                  return;
              }
              this._handleAfterChangeEvent(event);
              this.optionsStore.input?.dispatchEvent(
              //eslint-disable-next-line @typescript-eslint/no-explicit-any
              new CustomEvent('change', { detail: event }));
          }
          this.optionsStore.element.dispatchEvent(
          //eslint-disable-next-line @typescript-eslint/no-explicit-any
          new CustomEvent(event.type, { detail: event }));
          //eslint-disable-next-line @typescript-eslint/no-explicit-any
          if (window.jQuery) {
              //eslint-disable-next-line @typescript-eslint/no-explicit-any
              const $ = window.jQuery;
              if (isChangeEvent && this.optionsStore.input) {
                  $(this.optionsStore.input).trigger(event);
              }
              else {
                  $(this.optionsStore.element).trigger(event);
              }
          }
          this._publish(event);
      }
      _publish(event) {
          // return if event is not subscribed
          if (!Array.isArray(this._subscribers[event.type])) {
              return;
          }
          // Trigger callback for each subscriber
          this._subscribers[event.type].forEach((callback) => {
              callback(event);
          });
      }
      /**
       * Fires a ViewUpdate event when, for example, the month view is changed.
       * @private
       */
      _viewUpdate() {
          this._triggerEvent({
              type: Namespace.events.update,
              viewDate: this.optionsStore.viewDate.clone,
          });
      }
      _unsubscribe(eventName, index) {
          this._subscribers[eventName].splice(index, 1);
      }
      /**
       * Merges two Option objects together and validates options type
       * @param config new Options
       * @param mergeTo Options to merge into
       * @param includeDataset When true, the elements data-td attributes will be included in the
       * @private
       */
      _initializeOptions(config, mergeTo, includeDataset = false) {
          let newConfig = OptionConverter.deepCopy(config);
          newConfig = OptionConverter._mergeOptions(newConfig, mergeTo);
          if (includeDataset)
              newConfig = OptionConverter._dataToOptions(this.optionsStore.element, newConfig);
          OptionConverter._validateConflicts(newConfig);
          newConfig.viewDate = newConfig.viewDate.setLocalization(newConfig.localization);
          if (!this.optionsStore.viewDate.isSame(newConfig.viewDate)) {
              this.optionsStore.viewDate = newConfig.viewDate;
          }
          /**
           * Sets the minimum view allowed by the picker. For example the case of only
           * allowing year and month to be selected but not date.
           */
          if (newConfig.display.components.year) {
              this.optionsStore.minimumCalendarViewMode = 2;
          }
          if (newConfig.display.components.month) {
              this.optionsStore.minimumCalendarViewMode = 1;
          }
          if (newConfig.display.components.date) {
              this.optionsStore.minimumCalendarViewMode = 0;
          }
          this.optionsStore.currentCalendarViewMode = Math.max(this.optionsStore.minimumCalendarViewMode, this.optionsStore.currentCalendarViewMode);
          // Update view mode if needed
          if (CalendarModes[this.optionsStore.currentCalendarViewMode].name !==
              newConfig.display.viewMode) {
              this.optionsStore.currentCalendarViewMode = Math.max(CalendarModes.findIndex((x) => x.name === newConfig.display.viewMode), this.optionsStore.minimumCalendarViewMode);
          }
          if (this.display?.isVisible) {
              this.display._update('all');
          }
          if (newConfig.display.components.useTwentyfourHour &&
              newConfig.localization.hourCycle === undefined)
              newConfig.localization.hourCycle = 'h24';
          else if (newConfig.localization.hourCycle === undefined) {
              newConfig.localization.hourCycle = guessHourCycle(newConfig.localization.locale);
          }
          this.optionsStore.options = newConfig;
          if (newConfig.restrictions.maxDate &&
              this.viewDate.isAfter(newConfig.restrictions.maxDate))
              this.viewDate = newConfig.restrictions.maxDate.clone;
          if (newConfig.restrictions.minDate &&
              this.viewDate.isBefore(newConfig.restrictions.minDate))
              this.viewDate = newConfig.restrictions.minDate.clone;
      }
      /**
       * Checks if an input field is being used, attempts to locate one and sets an
       * event listener if found.
       * @private
       */
      _initializeInput() {
          if (this.optionsStore.element.tagName == 'INPUT') {
              this.optionsStore.input = this.optionsStore.element;
          }
          else {
              const query = this.optionsStore.element.dataset.tdTargetInput;
              if (query == undefined || query == 'nearest') {
                  this.optionsStore.input =
                      this.optionsStore.element.querySelector('input');
              }
              else {
                  this.optionsStore.input =
                      this.optionsStore.element.querySelector(query);
              }
          }
          if (!this.optionsStore.input)
              return;
          if (!this.optionsStore.input.value && this.optionsStore.options.defaultDate)
              this.optionsStore.input.value = this.dates.formatInput(this.optionsStore.options.defaultDate);
          this.optionsStore.input.addEventListener('change', this._inputChangeEvent);
          if (this.optionsStore.options.allowInputToggle) {
              this.optionsStore.input.addEventListener('click', this._openClickEvent);
              this.optionsStore.input.addEventListener('focus', this._openClickEvent);
          }
          if (this.optionsStore.input.value) {
              this._inputChangeEvent();
          }
      }
      /**
       * Attempts to locate a toggle for the picker and sets an event listener
       * @private
       */
      _initializeToggle() {
          if (this.optionsStore.options.display.inline)
              return;
          let query = this.optionsStore.element.dataset.tdTargetToggle;
          if (query == 'nearest') {
              query = '[data-td-toggle="datetimepicker"]';
          }
          this._toggle =
              query == undefined
                  ? this.optionsStore.element
                  : this.optionsStore.element.querySelector(query);
          this._toggle.addEventListener('click', this._toggleClickEvent);
      }
      /**
       * If the option is enabled this will render the clock view after a date pick.
       * @param e change event
       * @private
       */
      _handleAfterChangeEvent(e) {
          if (
          // options is disabled
          !this.optionsStore.options.promptTimeOnDateChange ||
              this.optionsStore.options.multipleDates ||
              this.optionsStore.options.display.inline ||
              this.optionsStore.options.display.sideBySide ||
              // time is disabled
              !this.display._hasTime ||
              // clock component is already showing
              this.display.widget
                  ?.getElementsByClassName(Namespace.css.show)[0]
                  .classList.contains(Namespace.css.timeContainer))
              return;
          // First time ever. If useCurrent option is set to true (default), do nothing
          // because the first date is selected automatically.
          // or date didn't change (time did) or date changed because time did.
          if ((!e.oldDate && this.optionsStore.options.useCurrent) ||
              (e.oldDate && e.date?.isSame(e.oldDate))) {
              return;
          }
          clearTimeout(this._currentPromptTimeTimeout);
          this._currentPromptTimeTimeout = setTimeout(() => {
              if (this.display.widget) {
                  this._eventEmitters.action.emit({
                      e: {
                          currentTarget: this.display.widget.querySelector('[data-action="togglePicker"]'),
                      },
                      action: ActionTypes$1.togglePicker,
                  });
              }
          }, this.optionsStore.options.promptTimeOnDateChangeTransitionDelay);
      }
  }
  /**
   * Whenever a locale is loaded via a plugin then store it here based on the
   * locale name. E.g. loadedLocales['ru']
   */
  const loadedLocales = {};
  // noinspection JSUnusedGlobalSymbols
  /**
   * Called from a locale plugin.
   * @param l locale object for localization options
   */
  const loadLocale = (l) => {
      if (loadedLocales[l.name])
          return;
      loadedLocales[l.name] = l.localization;
  };
  /**
   * A sets the global localization options to the provided locale name.
   * `loadLocale` MUST be called first.
   * @param l
   */
  const locale = (l) => {
      const asked = loadedLocales[l];
      if (!asked)
          return;
      DefaultOptions.localization = asked;
  };
  // noinspection JSUnusedGlobalSymbols
  /**
   * Called from a plugin to extend or override picker defaults.
   * @param plugin
   * @param option
   */
  const extend = function (plugin, option = undefined) {
      if (!plugin)
          return tempusDominus;
      if (!plugin.installed) {
          // install plugin only once
          plugin(option, { TempusDominus, Dates, Display, DateTime, Namespace }, tempusDominus);
          plugin.installed = true;
      }
      return tempusDominus;
  };
  const version = '6.9.4';
  const tempusDominus = {
      TempusDominus,
      extend,
      loadLocale,
      locale,
      Namespace,
      DefaultOptions,
      DateTime,
      Unit: exports.Unit,
      version,
      DefaultEnLocalization,
  };

  exports.DateTime = DateTime;
  exports.DefaultEnLocalization = DefaultEnLocalization;
  exports.DefaultOptions = DefaultOptions;
  exports.Namespace = Namespace;
  exports.TempusDominus = TempusDominus;
  exports.extend = extend;
  exports.loadLocale = loadLocale;
  exports.locale = locale;
  exports.version = version;

  Object.defineProperty(exports, '__esModule', { value: true });

}));
//# sourceMappingURL=tempus-dominus.js.map
