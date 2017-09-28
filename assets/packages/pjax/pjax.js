!function(e){if("object"==typeof exports)module.exports=e();else if("function"==typeof define&&define.amd)define(e);else{var f;"undefined"!=typeof window?f=window:"undefined"!=typeof global?f=global:"undefined"!=typeof self&&(f=self),f.Pjax=e()}}(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(_dereq_,module,exports){
var clone = _dereq_('./lib/clone.js')
var executeScripts = _dereq_('./lib/execute-scripts.js')

var forEachEls = _dereq_("./lib/foreach-els.js")

var newUid = _dereq_("./lib/uniqueid.js")

var on = _dereq_("./lib/events/on.js")
// var off = require("./lib/events/on.js")
var trigger = _dereq_("./lib/events/trigger.js")


var Pjax = function(options) {
    this.firstrun = true

    var parseOptions = _dereq_("./lib/proto/parse-options.js");
    parseOptions.apply(this,[options])
    this.log("Pjax options", this.options)

    this.maxUid = this.lastUid = newUid()

    this.parseDOM(document)

    on(window, "popstate", function(st) {
      if (st.state) {
        var opt = clone(this.options)
        opt.url = st.state.url
        opt.title = st.state.title
        opt.history = false
        opt.requestOptions = {};
        if (st.state.uid < this.lastUid) {
          opt.backward = true
        }
        else {
          opt.forward = true
        }
        this.lastUid = st.state.uid

        // @todo implement history cache here, based on uid
        this.loadUrl(st.state.url, opt)
      }
    }.bind(this))
  }

Pjax.prototype = {
  log: _dereq_("./lib/proto/log.js"),

  getElements: _dereq_("./lib/proto/get-elements.js"),

  parseDOM: _dereq_("./lib/proto/parse-dom.js"),

  refresh: _dereq_("./lib/proto/refresh.js"),

  reload: _dereq_("./lib/reload.js"),

  attachLink: _dereq_("./lib/proto/attach-link.js"),

  attachForm: _dereq_("./lib/proto/attach-form.js"),

  forEachSelectors: function(cb, context, DOMcontext) {
    return _dereq_("./lib/foreach-selectors.js").bind(this)(this.options.selectors, cb, context, DOMcontext)
  },

  switchSelectors: function(selectors, fromEl, toEl, options) {
    return _dereq_("./lib/switches-selectors.js").bind(this)(this.options.switches, this.options.switchesOptions, selectors, fromEl, toEl, options)
  },

  // too much problem with the code below
  // + it’s too dangerous
//   switchFallback: function(fromEl, toEl) {
//     this.switchSelectors(["head", "body"], fromEl, toEl)
//     // execute script when DOM is like it should be
//     Pjax.executeScripts(document.querySelector("head"))
//     Pjax.executeScripts(document.querySelector("body"))
//   }

  latestChance: function(href) {
    window.location = href
  },

  onSwitch: function() {
    trigger(window, "resize scroll")
  },

  loadContent: function(html, options) {
    var tmpEl = document.implementation.createHTMLDocument("")

    // parse HTML attributes to copy them
    // since we are forced to use documentElement.innerHTML (outerHTML can't be used for <html>)
    var htmlRegex = /<html[^>]+>/gi
    var htmlAttribsRegex = /\s?[a-z:]+(?:\=(?:\'|\")[^\'\">]+(?:\'|\"))*/gi
    var matches = html.match(htmlRegex)
    if (matches && matches.length) {
      matches = matches[0].match(htmlAttribsRegex)
      if (matches.length) {
        matches.shift()
        matches.forEach(function(htmlAttrib) {
          var attr = htmlAttrib.trim().split("=")
          if (attr.length === 1) {
            tmpEl.documentElement.setAttribute(attr[0], true)
          }
          else {
            tmpEl.documentElement.setAttribute(attr[0], attr[1].slice(1, -1))
          }
        })
      }
    }

    tmpEl.documentElement.innerHTML = html
    this.log("load content", tmpEl.documentElement.attributes, tmpEl.documentElement.innerHTML.length)

    // Clear out any focused controls before inserting new page contents.
    // we clear focus on non form elements
    if (document.activeElement && !document.activeElement.value) {
      try {
        document.activeElement.blur()
      } catch (e) { }
    }

    // try {
    this.switchSelectors(this.options.selectors, tmpEl, document, options)

    // FF bug: Won’t autofocus fields that are inserted via JS.
    // This behavior is incorrect. So if theres no current focus, autofocus
    // the last field.
    //
    // http://www.w3.org/html/wg/drafts/html/master/forms.html
    var autofocusEl = Array.prototype.slice.call(document.querySelectorAll("[autofocus]")).pop()
    if (autofocusEl && document.activeElement !== autofocusEl) {
      autofocusEl.focus();
    }

    // execute scripts when DOM have been completely updated
    this.options.selectors.forEach(function(selector) {
      forEachEls(document.querySelectorAll(selector), function(el) {
        executeScripts(el)
      })
    })
    // }
    // catch(e) {
    //   if (this.options.debug) {
    //     this.log("Pjax switch fail: ", e)
    //   }
    //   this.switchFallback(tmpEl, document)
    // }
  },

  doRequest: _dereq_("./lib/request.js"),

  loadUrl: function(href, options) {
    this.log("load href", href, options)

    trigger(document, "pjax:send", options);

    // Do the request
    this.doRequest(href, options.requestOptions, function(html) {
      // Fail if unable to load HTML via AJAX
      if (html === false) {
        trigger(document,"pjax:complete pjax:error", options)

        return
      }

      // Clear out any focused controls before inserting new page contents.
      document.activeElement.blur()

      try {
        this.loadContent(html, options)
      }
      catch (e) {
        if (!this.options.debug) {
          if (console && console.error) {
            console.error("Pjax switch fail: ", e)
          }
          this.latestChance(href)
          return
        }
        else {
          throw e
        }
      }

      if (options.history) {
        if (this.firstrun) {
          this.lastUid = this.maxUid = newUid()
          this.firstrun = false
          window.history.replaceState({
            url: window.location.href,
            title: document.title,
            uid: this.maxUid
          },
          document.title)
        }

        // Update browser history
        this.lastUid = this.maxUid = newUid()
        window.history.pushState({
          url: href,
          title: options.title,
          uid: this.maxUid
        },
          options.title,
          href)
      }

      this.forEachSelectors(function(el) {
        this.parseDOM(el)
      }, this)

      // Fire Events
      trigger(document,"pjax:complete pjax:success", options)

      options.analytics()

      // Scroll page to top on new page load
      if (options.scrollTo !== false) {
        if (options.scrollTo.length > 1) {
          window.scrollTo(options.scrollTo[0], options.scrollTo[1])
        }
        else {
          window.scrollTo(0, options.scrollTo)
        }
      }
    }.bind(this))
  }
}

Pjax.isSupported = _dereq_("./lib/is-supported.js");

//arguably could do `if( require("./lib/is-supported.js")()) {` but that might be a little to simple
if (Pjax.isSupported()) {
  module.exports = Pjax
}
// if there isn’t required browser functions, returning stupid api
else {
  var stupidPjax = function() {}
  for (var key in Pjax.prototype) {
    if (Pjax.prototype.hasOwnProperty(key) && typeof Pjax.prototype[key] === "function") {
      stupidPjax[key] = stupidPjax
    }
  }

  module.exports = stupidPjax
}

},{"./lib/clone.js":2,"./lib/events/on.js":4,"./lib/events/trigger.js":5,"./lib/execute-scripts.js":6,"./lib/foreach-els.js":7,"./lib/foreach-selectors.js":8,"./lib/is-supported.js":9,"./lib/proto/attach-form.js":11,"./lib/proto/attach-link.js":12,"./lib/proto/get-elements.js":13,"./lib/proto/log.js":14,"./lib/proto/parse-dom.js":15,"./lib/proto/parse-options.js":17,"./lib/proto/refresh.js":18,"./lib/reload.js":19,"./lib/request.js":20,"./lib/switches-selectors.js":21,"./lib/uniqueid.js":23}],2:[function(_dereq_,module,exports){
module.exports = function(obj) {
  if (null === obj || "object" != typeof obj) {
    return obj
  }
  var copy = obj.constructor()
  for (var attr in obj) {
    if (obj.hasOwnProperty(attr)) {
      copy[attr] = obj[attr]
    }
  }
  return copy
}

},{}],3:[function(_dereq_,module,exports){
module.exports = function(el) {
  // console.log("going to execute script", el)

  var code = (el.text || el.textContent || el.innerHTML || "")
  var parent = el.parentNode || document.querySelector("head") || document.documentElement
  var script = document.createElement("script")

  if (code.match("document.write")) {
    if (console && console.log) {
      console.log("Script contains document.write. Can’t be executed correctly. Code skipped ", el)
    }
    return false
  }

  script.type = "text/javascript"
  try {
    script.appendChild(document.createTextNode(code))
  }
  catch (e) {
    // old IEs have funky script nodes
    script.text = code
  }

  // execute
  parent.appendChild(script);
  // avoid pollution only in head or body tags
  if (["head","body"].indexOf(parent.tagName.toLowerCase()) > 0) {
    parent.removeChild(script)
  }

  return true
}

},{}],4:[function(_dereq_,module,exports){
var forEachEls = _dereq_("../foreach-els")

module.exports = function(els, events, listener, useCapture) {
  events = (typeof events === "string" ? events.split(" ") : events)

  events.forEach(function(e) {
    forEachEls(els, function(el) {
      el.addEventListener(e, listener, useCapture)
    })
  })
}

},{"../foreach-els":7}],5:[function(_dereq_,module,exports){
var forEachEls = _dereq_("../foreach-els")

module.exports = function(els, events, opts) {
  events = (typeof events === "string" ? events.split(" ") : events)

  events.forEach(function(e) {
    var event // = new CustomEvent(e) // doesn't everywhere yet
    event = document.createEvent("HTMLEvents")
    event.initEvent(e, true, true)
    event.eventName = e
    if (opts) {
      Object.keys(opts).forEach(function(key) {
        event[key] = opts[key]
      })
    }

    forEachEls(els, function(el) {
      var domFix = false
      if (!el.parentNode && el !== document && el !== window) {
        // THANKS YOU IE (9/10//11 concerned)
        // dispatchEvent doesn't work if element is not in the dom
        domFix = true
        document.body.appendChild(el)
      }
      el.dispatchEvent(event)
      if (domFix) {
        el.parentNode.removeChild(el)
      }
    })
  })
}

},{"../foreach-els":7}],6:[function(_dereq_,module,exports){
var forEachEls = _dereq_("./foreach-els")
var evalScript = _dereq_("./eval-script")
// Finds and executes scripts (used for newly added elements)
// Needed since innerHTML does not run scripts
module.exports = function(el) {
  // console.log("going to execute scripts for ", el)

  if (el.tagName.toLowerCase() === "script") {
    evalScript(el);
  }

  forEachEls(el.querySelectorAll("script"), function(script) {
    if (!script.type || script.type.toLowerCase() === "text/javascript") {
      if (script.parentNode) {
        script.parentNode.removeChild(script)
      }
      evalScript(script)
    }
  })
}

},{"./eval-script":3,"./foreach-els":7}],7:[function(_dereq_,module,exports){
/* global HTMLCollection: true */

module.exports = function(els, fn, context) {
  if (els instanceof HTMLCollection || els instanceof NodeList || els instanceof Array) {
    return Array.prototype.forEach.call(els, fn, context)
  }
  // assume simple dom element
  return fn.call(context, els)
}

},{}],8:[function(_dereq_,module,exports){
var forEachEls = _dereq_("./foreach-els")

module.exports = function(selectors, cb, context, DOMcontext) {
  DOMcontext = DOMcontext || document
  selectors.forEach(function(selector) {
    forEachEls(DOMcontext.querySelectorAll(selector), cb, context)
  })
}

},{"./foreach-els":7}],9:[function(_dereq_,module,exports){
module.exports = function() {
  // Borrowed wholesale from https://github.com/defunkt/jquery-pjax
  return window.history &&
    window.history.pushState &&
    window.history.replaceState &&
    // pushState isn’t reliable on iOS until 5.
    !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]\D|WebApps\/.+CFNetwork)/)
}

},{}],10:[function(_dereq_,module,exports){
if (!Function.prototype.bind) {
  Function.prototype.bind = function(oThis) {
    if (typeof this !== "function") {
      // closest thing possible to the ECMAScript 5 internal IsCallable function
      throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable")
    }

    var aArgs = Array.prototype.slice.call(arguments, 1)
    var that = this
    var Fnoop = function() {}
    var fBound = function() {
      return that.apply(this instanceof Fnoop && oThis ? this : oThis, aArgs.concat(Array.prototype.slice.call(arguments)))
    }

    Fnoop.prototype = this.prototype
    fBound.prototype = new Fnoop()

    return fBound
  }
}

},{}],11:[function(_dereq_,module,exports){
_dereq_("../polyfills/Function.prototype.bind")

var on = _dereq_("../events/on")
var clone = _dereq_("../clone")

var attrClick = "data-pjax-click-state"
var attrKey = "data-pjax-keyup-state"

var formAction = function(el, event){

  this.options.requestOptions = {
    requestUrl : el.getAttribute('action') || window.location.href,
    requestMethod : el.getAttribute('method') || 'GET',
  }

  //create a testable virtual link of the form action
  var virtLinkElement = document.createElement('a');
  virtLinkElement.setAttribute('href', this.options.requestOptions.requestUrl);

  // Ignore external links.
  if (virtLinkElement.protocol !== window.location.protocol || virtLinkElement.host !== window.location.host) {
    el.setAttribute(attrClick, "external");
    return
  }

  // Ignore click if we are on an anchor on the same page
  if (virtLinkElement.pathname === window.location.pathname && virtLinkElement.hash.length > 0) {
    el.setAttribute(attrClick, "anchor-present");
    return
  }

  // if declared as a full reload, just normally submit the form
  if ( this.options.currentUrlFullReload) {
    el.setAttribute(attrClick, "reload");
    return;
  }

  event.preventDefault()

  var paramObject = [];
  for(var elementKey in el.elements) {
    var element = el.elements[elementKey];
    if (!!element.name && element.attributes !== undefined && element.tagName.toLowerCase() !== 'button'){
      if ((element.attributes.type !== 'checkbox' && element.attributes.type !== 'radio') || element.checked) {
        paramObject.push({ name: encodeURIComponent(element.name), value: encodeURIComponent(element.value)});
      }
    }
  }

  //Creating a getString
  var paramsString = (paramObject.map(function(value){return value.name+"="+value.value;})).join('&');

  this.options.requestOptions.requestPayload = paramObject;
  this.options.requestOptions.requestPayloadString = paramsString;

  this.loadUrl(virtLinkElement.href, clone(this.options))

};

var isDefaultPrevented = function(event) {
  return event.defaultPrevented || event.returnValue === false;
};


module.exports = function(el) {
  var that = this

  on(el, "submit", function(event) {
    if (isDefaultPrevented(event)) {
      return
    }

    formAction.call(that, el, event)
  })

  on(el, "keyup", function(event) {
    if (isDefaultPrevented(event)) {
      return
    }

    // Don’t break browser special behavior on links (like page in new window)
    if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      el.setAttribute(attrKey, "modifier")
      return
    }

    if (event.keyCode == 13) {
      formAction.call(that, el, event)
    }
  }.bind(this))
}

},{"../clone":2,"../events/on":4,"../polyfills/Function.prototype.bind":10}],12:[function(_dereq_,module,exports){
_dereq_("../polyfills/Function.prototype.bind")

var on = _dereq_("../events/on")
var clone = _dereq_("../clone")

var attrClick = "data-pjax-click-state"
var attrKey = "data-pjax-keyup-state"

var linkAction = function(el, event) {
  // Don’t break browser special behavior on links (like page in new window)
  if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
    el.setAttribute(attrClick, "modifier")
    return
  }

  // we do test on href now to prevent unexpected behavior if for some reason
  // user have href that can be dynamically updated

  // Ignore external links.
  if (el.protocol !== window.location.protocol || el.host !== window.location.host) {
    el.setAttribute(attrClick, "external")
    return
  }

  // Ignore click if we are on an anchor on the same page
  if (el.pathname === window.location.pathname && el.hash.length > 0) {
    el.setAttribute(attrClick, "anchor-present")
    return
  }

  // Ignore anchors on the same page (keep native behavior)
  if (el.hash && el.href.replace(el.hash, "") === window.location.href.replace(location.hash, "")) {
    el.setAttribute(attrClick, "anchor")
    return
  }

  // Ignore empty anchor "foo.html#"
  if (el.href === window.location.href.split("#")[0] + "#") {
    el.setAttribute(attrClick, "anchor-empty")
    return
  }

  event.preventDefault()

  // don’t do "nothing" if user try to reload the page by clicking the same link twice
  if (
    this.options.currentUrlFullReload &&
    el.href === window.location.href.split("#")[0]
  ) {
    el.setAttribute(attrClick, "reload")
    this.reload()
    return
  }
  this.options.requestOptions = this.options.requestOptions || {};
  el.setAttribute(attrClick, "load")
  this.loadUrl(el.href, clone(this.options))
}

var isDefaultPrevented = function(event) {
  return event.defaultPrevented || event.returnValue === false;
}

module.exports = function(el) {
  var that = this

  on(el, "click", function(event) {
    if (isDefaultPrevented(event)) {
      return
    }

    linkAction.call(that, el, event)
  })

  on(el, "keyup", function(event) {
    if (isDefaultPrevented(event)) {
      return
    }

    // Don’t break browser special behavior on links (like page in new window)
    if (event.which > 1 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
      el.setAttribute(attrKey, "modifier")
      return
    }

    if (event.keyCode == 13) {
      linkAction.call(that, el, event)
    }
  }.bind(this))
}

},{"../clone":2,"../events/on":4,"../polyfills/Function.prototype.bind":10}],13:[function(_dereq_,module,exports){
module.exports = function(el) {
  return el.querySelectorAll(this.options.elements)
}

},{}],14:[function(_dereq_,module,exports){
module.exports = function() {
  if (this.options.debug && console) {
    if (typeof console.log === "function") {
      console.log.apply(console, arguments);
    }
    // ie is weird
    else if (console.log) {
      console.log(arguments);
    }
  }
}

},{}],15:[function(_dereq_,module,exports){
var forEachEls = _dereq_("../foreach-els")

var parseElement = _dereq_("./parse-element")

module.exports = function(el) {
  forEachEls(this.getElements(el), parseElement, this)
}

},{"../foreach-els":7,"./parse-element":16}],16:[function(_dereq_,module,exports){
module.exports = function(el) {
  switch (el.tagName.toLowerCase()) {
  case "a":
    // only attach link if el does not already have link attached
    if (!el.hasAttribute('data-pjax-click-state')) {
      this.attachLink(el)
    }
    break

    case "form":
      // only attach link if el does not already have link attached
      if (!el.hasAttribute('data-pjax-click-state')) {
        this.attachForm(el)
      }
    break

  default:
    throw "Pjax can only be applied on <a> or <form> submit"
  }
}

},{}],17:[function(_dereq_,module,exports){
/* global _gaq: true, ga: true */

module.exports = function(options){
  this.options = options
  this.options.elements = this.options.elements || "a[href], form[action]"
  this.options.selectors = this.options.selectors || ["title", ".js-Pjax"]
  this.options.switches = this.options.switches || {}
  this.options.switchesOptions = this.options.switchesOptions || {}
  this.options.history = this.options.history || true
  this.options.analytics = this.options.analytics || function() {
    // options.backward or options.foward can be true or undefined
    // by default, we do track back/foward hit
    // https://productforums.google.com/forum/#!topic/analytics/WVwMDjLhXYk
    if (window._gaq) {
      _gaq.push(["_trackPageview"])
    }
    if (window.ga) {
      ga("send", "pageview", {page: location.pathname, title: document.title})
    }
  }
  this.options.scrollTo = (typeof this.options.scrollTo === 'undefined') ? 0 : this.options.scrollTo;
  this.options.cacheBust = (typeof this.options.cacheBust === 'undefined') ? true : this.options.cacheBust
  this.options.debug = this.options.debug || false

  // we can’t replace body.outerHTML or head.outerHTML
  // it create a bug where new body or new head are created in the dom
  // if you set head.outerHTML, a new body tag is appended, so the dom get 2 body
  // & it break the switchFallback which replace head & body
  if (!this.options.switches.head) {
    this.options.switches.head = this.switchElementsAlt
  }
  if (!this.options.switches.body) {
    this.options.switches.body = this.switchElementsAlt
  }
  if (typeof options.analytics !== "function") {
    options.analytics = function() {}
  }
}
},{}],18:[function(_dereq_,module,exports){
module.exports = function(el) {
  this.parseDOM(el || document)
}

},{}],19:[function(_dereq_,module,exports){
module.exports = function() {
  window.location.reload()
}

},{}],20:[function(_dereq_,module,exports){
module.exports = function(location, options, callback) {
  options = options || {};
  var requestMethod = options.requestMethod || "GET";
  var requestPayload = options.requestPayloadString || null;
  var request = new XMLHttpRequest()

  request.onreadystatechange = function() {
    if (request.readyState === 4) {
      if (request.status === 200) {
        callback(request.responseText, request)
      }
      else {
        callback(null, request)
      }
    }
  }

  // Add a timestamp as part of the query string if cache busting is enabled
  if (this.options.cacheBust) {
    location += (!/[?&]/.test(location) ? "?" : "&") + new Date().getTime()
  }

  request.open(requestMethod.toUpperCase(), location, true)
  request.setRequestHeader("X-Requested-With", "XMLHttpRequest")

  // Add the request payload if available
  if (options.requestPayloadString != undefined && options.requestPayloadString != "") {
    // Send the proper header information along with the request
    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  }

  request.send(requestPayload)

  return request
}

},{}],21:[function(_dereq_,module,exports){
var forEachEls = _dereq_("./foreach-els")

var defaultSwitches = _dereq_("./switches")

module.exports = function(switches, switchesOptions, selectors, fromEl, toEl, options) {
  selectors.forEach(function(selector) {
    var newEls = fromEl.querySelectorAll(selector)
    var oldEls = toEl.querySelectorAll(selector)
    if (this.log) {
      this.log("Pjax switch", selector, newEls, oldEls)
    }
    if (newEls.length !== oldEls.length) {
      // forEachEls(newEls, function(el) {
      //   this.log("newEl", el, el.outerHTML)
      // }, this)
      // forEachEls(oldEls, function(el) {
      //   this.log("oldEl", el, el.outerHTML)
      // }, this)
      throw "DOM doesn’t look the same on new loaded page: ’" + selector + "’ - new " + newEls.length + ", old " + oldEls.length
    }

    forEachEls(newEls, function(newEl, i) {
      var oldEl = oldEls[i]
      if (this.log) {
        this.log("newEl", newEl, "oldEl", oldEl)
      }
      if (switches[selector]) {
        switches[selector].bind(this)(oldEl, newEl, options, switchesOptions[selector])
      }
      else {
        defaultSwitches.outerHTML.bind(this)(oldEl, newEl, options)
      }
    }, this)
  }, this)
}

},{"./foreach-els":7,"./switches":22}],22:[function(_dereq_,module,exports){
var on = _dereq_("./events/on.js")
// var off = require("./lib/events/on.js")
// var trigger = require("./lib/events/trigger.js")


module.exports = {
  outerHTML: function(oldEl, newEl) {
    oldEl.outerHTML = newEl.outerHTML
    this.onSwitch()
  },

  innerHTML: function(oldEl, newEl) {
    oldEl.innerHTML = newEl.innerHTML
    oldEl.className = newEl.className
    this.onSwitch()
  },

  sideBySide: function(oldEl, newEl, options, switchOptions) {
    var forEach = Array.prototype.forEach
    var elsToRemove = []
    var elsToAdd = []
    var fragToAppend = document.createDocumentFragment()
    // height transition are shitty on safari
    // so commented for now (until I found something ?)
    // var relevantHeight = 0
    var animationEventNames = "animationend webkitAnimationEnd MSAnimationEnd oanimationend"
    var animatedElsNumber = 0
    var sexyAnimationEnd = function(e) {
          if (e.target != e.currentTarget) {
            // end triggered by an animation on a child
            return
          }

          animatedElsNumber--
          if (animatedElsNumber <= 0 && elsToRemove) {
            elsToRemove.forEach(function(el) {
              // browsing quickly can make the el
              // already removed by last page update ?
              if (el.parentNode) {
                el.parentNode.removeChild(el)
              }
            })

            elsToAdd.forEach(function(el) {
              el.className = el.className.replace(el.getAttribute("data-pjax-classes"), "")
              el.removeAttribute("data-pjax-classes")
              // Pjax.off(el, animationEventNames, sexyAnimationEnd, true)
            })

            elsToAdd = null // free memory
            elsToRemove = null // free memory

            // assume the height is now useless (avoid bug since there is overflow hidden on the parent)
            // oldEl.style.height = "auto"

            // this is to trigger some repaint (example: picturefill)
            this.onSwitch()
            // Pjax.trigger(window, "scroll")
          }
        }.bind(this)

    // Force height to be able to trigger css animation
    // here we get the relevant height
    // oldEl.parentNode.appendChild(newEl)
    // relevantHeight = newEl.getBoundingClientRect().height
    // oldEl.parentNode.removeChild(newEl)
    // oldEl.style.height = oldEl.getBoundingClientRect().height + "px"

    switchOptions = switchOptions || {}

    forEach.call(oldEl.childNodes, function(el) {
      elsToRemove.push(el)
      if (el.classList && !el.classList.contains("js-Pjax-remove")) {
        // for fast switch, clean element that just have been added, & not cleaned yet.
        if (el.hasAttribute("data-pjax-classes")) {
          el.className = el.className.replace(el.getAttribute("data-pjax-classes"), "")
          el.removeAttribute("data-pjax-classes")
        }
        el.classList.add("js-Pjax-remove")
        if (switchOptions.callbacks && switchOptions.callbacks.removeElement) {
          switchOptions.callbacks.removeElement(el)
        }
        if (switchOptions.classNames) {
          el.className += " " + switchOptions.classNames.remove + " " + (options.backward ? switchOptions.classNames.backward : switchOptions.classNames.forward)
        }
        animatedElsNumber++
        on(el, animationEventNames, sexyAnimationEnd, true)
      }
    })

    forEach.call(newEl.childNodes, function(el) {
      if (el.classList) {
        var addClasses = ""
        if (switchOptions.classNames) {
          addClasses = " js-Pjax-add " + switchOptions.classNames.add + " " + (options.backward ? switchOptions.classNames.forward : switchOptions.classNames.backward)
        }
        if (switchOptions.callbacks && switchOptions.callbacks.addElement) {
          switchOptions.callbacks.addElement(el)
        }
        el.className += addClasses
        el.setAttribute("data-pjax-classes", addClasses)
        elsToAdd.push(el)
        fragToAppend.appendChild(el)
        animatedElsNumber++
        on(el, animationEventNames, sexyAnimationEnd, true)
      }
    })

    // pass all className of the parent
    oldEl.className = newEl.className
    oldEl.appendChild(fragToAppend)

    // oldEl.style.height = relevantHeight + "px"
  }
}

},{"./events/on.js":4}],23:[function(_dereq_,module,exports){
module.exports = (function() {
  var counter = 0
  return function() {
    var id = ("pjax" + (new Date().getTime())) + "_" + counter
    counter++
    return id
  }
})()

},{}]},{},[1])
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi9vcHQvd2ViL3BqYXgvbm9kZV9tb2R1bGVzL2Jyb3dzZXJpZnkvbm9kZV9tb2R1bGVzL2Jyb3dzZXItcGFjay9fcHJlbHVkZS5qcyIsIi9vcHQvd2ViL3BqYXgvaW5kZXguanMiLCIvb3B0L3dlYi9wamF4L2xpYi9jbG9uZS5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL2V2YWwtc2NyaXB0LmpzIiwiL29wdC93ZWIvcGpheC9saWIvZXZlbnRzL29uLmpzIiwiL29wdC93ZWIvcGpheC9saWIvZXZlbnRzL3RyaWdnZXIuanMiLCIvb3B0L3dlYi9wamF4L2xpYi9leGVjdXRlLXNjcmlwdHMuanMiLCIvb3B0L3dlYi9wamF4L2xpYi9mb3JlYWNoLWVscy5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL2ZvcmVhY2gtc2VsZWN0b3JzLmpzIiwiL29wdC93ZWIvcGpheC9saWIvaXMtc3VwcG9ydGVkLmpzIiwiL29wdC93ZWIvcGpheC9saWIvcG9seWZpbGxzL0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kLmpzIiwiL29wdC93ZWIvcGpheC9saWIvcHJvdG8vYXR0YWNoLWZvcm0uanMiLCIvb3B0L3dlYi9wamF4L2xpYi9wcm90by9hdHRhY2gtbGluay5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL3Byb3RvL2dldC1lbGVtZW50cy5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL3Byb3RvL2xvZy5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL3Byb3RvL3BhcnNlLWRvbS5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL3Byb3RvL3BhcnNlLWVsZW1lbnQuanMiLCIvb3B0L3dlYi9wamF4L2xpYi9wcm90by9wYXJzZS1vcHRpb25zLmpzIiwiL29wdC93ZWIvcGpheC9saWIvcHJvdG8vcmVmcmVzaC5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL3JlbG9hZC5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL3JlcXVlc3QuanMiLCIvb3B0L3dlYi9wamF4L2xpYi9zd2l0Y2hlcy1zZWxlY3RvcnMuanMiLCIvb3B0L3dlYi9wamF4L2xpYi9zd2l0Y2hlcy5qcyIsIi9vcHQvd2ViL3BqYXgvbGliL3VuaXF1ZWlkLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwUEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ2hDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMvQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNUQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMzRkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pGQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNQQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDckNBO0FBQ0E7QUFDQTtBQUNBOztBQ0hBO0FBQ0E7QUFDQTtBQUNBOztBQ0hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25DQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25IQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dGhyb3cgbmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKX12YXIgZj1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwoZi5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxmLGYuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwidmFyIGNsb25lID0gcmVxdWlyZSgnLi9saWIvY2xvbmUuanMnKVxudmFyIGV4ZWN1dGVTY3JpcHRzID0gcmVxdWlyZSgnLi9saWIvZXhlY3V0ZS1zY3JpcHRzLmpzJylcblxudmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9saWIvZm9yZWFjaC1lbHMuanNcIilcblxudmFyIG5ld1VpZCA9IHJlcXVpcmUoXCIuL2xpYi91bmlxdWVpZC5qc1wiKVxuXG52YXIgb24gPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL29uLmpzXCIpXG4vLyB2YXIgb2ZmID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy9vbi5qc1wiKVxudmFyIHRyaWdnZXIgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL3RyaWdnZXIuanNcIilcblxuXG52YXIgUGpheCA9IGZ1bmN0aW9uKG9wdGlvbnMpIHtcbiAgICB0aGlzLmZpcnN0cnVuID0gdHJ1ZVxuXG4gICAgdmFyIHBhcnNlT3B0aW9ucyA9IHJlcXVpcmUoXCIuL2xpYi9wcm90by9wYXJzZS1vcHRpb25zLmpzXCIpO1xuICAgIHBhcnNlT3B0aW9ucy5hcHBseSh0aGlzLFtvcHRpb25zXSlcbiAgICB0aGlzLmxvZyhcIlBqYXggb3B0aW9uc1wiLCB0aGlzLm9wdGlvbnMpXG5cbiAgICB0aGlzLm1heFVpZCA9IHRoaXMubGFzdFVpZCA9IG5ld1VpZCgpXG5cbiAgICB0aGlzLnBhcnNlRE9NKGRvY3VtZW50KVxuXG4gICAgb24od2luZG93LCBcInBvcHN0YXRlXCIsIGZ1bmN0aW9uKHN0KSB7XG4gICAgICBpZiAoc3Quc3RhdGUpIHtcbiAgICAgICAgdmFyIG9wdCA9IGNsb25lKHRoaXMub3B0aW9ucylcbiAgICAgICAgb3B0LnVybCA9IHN0LnN0YXRlLnVybFxuICAgICAgICBvcHQudGl0bGUgPSBzdC5zdGF0ZS50aXRsZVxuICAgICAgICBvcHQuaGlzdG9yeSA9IGZhbHNlXG4gICAgICAgIG9wdC5yZXF1ZXN0T3B0aW9ucyA9IHt9O1xuICAgICAgICBpZiAoc3Quc3RhdGUudWlkIDwgdGhpcy5sYXN0VWlkKSB7XG4gICAgICAgICAgb3B0LmJhY2t3YXJkID0gdHJ1ZVxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgIG9wdC5mb3J3YXJkID0gdHJ1ZVxuICAgICAgICB9XG4gICAgICAgIHRoaXMubGFzdFVpZCA9IHN0LnN0YXRlLnVpZFxuXG4gICAgICAgIC8vIEB0b2RvIGltcGxlbWVudCBoaXN0b3J5IGNhY2hlIGhlcmUsIGJhc2VkIG9uIHVpZFxuICAgICAgICB0aGlzLmxvYWRVcmwoc3Quc3RhdGUudXJsLCBvcHQpXG4gICAgICB9XG4gICAgfS5iaW5kKHRoaXMpKVxuICB9XG5cblBqYXgucHJvdG90eXBlID0ge1xuICBsb2c6IHJlcXVpcmUoXCIuL2xpYi9wcm90by9sb2cuanNcIiksXG5cbiAgZ2V0RWxlbWVudHM6IHJlcXVpcmUoXCIuL2xpYi9wcm90by9nZXQtZWxlbWVudHMuanNcIiksXG5cbiAgcGFyc2VET006IHJlcXVpcmUoXCIuL2xpYi9wcm90by9wYXJzZS1kb20uanNcIiksXG5cbiAgcmVmcmVzaDogcmVxdWlyZShcIi4vbGliL3Byb3RvL3JlZnJlc2guanNcIiksXG5cbiAgcmVsb2FkOiByZXF1aXJlKFwiLi9saWIvcmVsb2FkLmpzXCIpLFxuXG4gIGF0dGFjaExpbms6IHJlcXVpcmUoXCIuL2xpYi9wcm90by9hdHRhY2gtbGluay5qc1wiKSxcblxuICBhdHRhY2hGb3JtOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vYXR0YWNoLWZvcm0uanNcIiksXG5cbiAgZm9yRWFjaFNlbGVjdG9yczogZnVuY3Rpb24oY2IsIGNvbnRleHQsIERPTWNvbnRleHQpIHtcbiAgICByZXR1cm4gcmVxdWlyZShcIi4vbGliL2ZvcmVhY2gtc2VsZWN0b3JzLmpzXCIpLmJpbmQodGhpcykodGhpcy5vcHRpb25zLnNlbGVjdG9ycywgY2IsIGNvbnRleHQsIERPTWNvbnRleHQpXG4gIH0sXG5cbiAgc3dpdGNoU2VsZWN0b3JzOiBmdW5jdGlvbihzZWxlY3RvcnMsIGZyb21FbCwgdG9FbCwgb3B0aW9ucykge1xuICAgIHJldHVybiByZXF1aXJlKFwiLi9saWIvc3dpdGNoZXMtc2VsZWN0b3JzLmpzXCIpLmJpbmQodGhpcykodGhpcy5vcHRpb25zLnN3aXRjaGVzLCB0aGlzLm9wdGlvbnMuc3dpdGNoZXNPcHRpb25zLCBzZWxlY3RvcnMsIGZyb21FbCwgdG9FbCwgb3B0aW9ucylcbiAgfSxcblxuICAvLyB0b28gbXVjaCBwcm9ibGVtIHdpdGggdGhlIGNvZGUgYmVsb3dcbiAgLy8gKyBpdOKAmXMgdG9vIGRhbmdlcm91c1xuLy8gICBzd2l0Y2hGYWxsYmFjazogZnVuY3Rpb24oZnJvbUVsLCB0b0VsKSB7XG4vLyAgICAgdGhpcy5zd2l0Y2hTZWxlY3RvcnMoW1wiaGVhZFwiLCBcImJvZHlcIl0sIGZyb21FbCwgdG9FbClcbi8vICAgICAvLyBleGVjdXRlIHNjcmlwdCB3aGVuIERPTSBpcyBsaWtlIGl0IHNob3VsZCBiZVxuLy8gICAgIFBqYXguZXhlY3V0ZVNjcmlwdHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcImhlYWRcIikpXG4vLyAgICAgUGpheC5leGVjdXRlU2NyaXB0cyhkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiYm9keVwiKSlcbi8vICAgfVxuXG4gIGxhdGVzdENoYW5jZTogZnVuY3Rpb24oaHJlZikge1xuICAgIHdpbmRvdy5sb2NhdGlvbiA9IGhyZWZcbiAgfSxcblxuICBvblN3aXRjaDogZnVuY3Rpb24oKSB7XG4gICAgdHJpZ2dlcih3aW5kb3csIFwicmVzaXplIHNjcm9sbFwiKVxuICB9LFxuXG4gIGxvYWRDb250ZW50OiBmdW5jdGlvbihodG1sLCBvcHRpb25zKSB7XG4gICAgdmFyIHRtcEVsID0gZG9jdW1lbnQuaW1wbGVtZW50YXRpb24uY3JlYXRlSFRNTERvY3VtZW50KFwiXCIpXG5cbiAgICAvLyBwYXJzZSBIVE1MIGF0dHJpYnV0ZXMgdG8gY29weSB0aGVtXG4gICAgLy8gc2luY2Ugd2UgYXJlIGZvcmNlZCB0byB1c2UgZG9jdW1lbnRFbGVtZW50LmlubmVySFRNTCAob3V0ZXJIVE1MIGNhbid0IGJlIHVzZWQgZm9yIDxodG1sPilcbiAgICB2YXIgaHRtbFJlZ2V4ID0gLzxodG1sW14+XSs+L2dpXG4gICAgdmFyIGh0bWxBdHRyaWJzUmVnZXggPSAvXFxzP1thLXo6XSsoPzpcXD0oPzpcXCd8XFxcIilbXlxcJ1xcXCI+XSsoPzpcXCd8XFxcIikpKi9naVxuICAgIHZhciBtYXRjaGVzID0gaHRtbC5tYXRjaChodG1sUmVnZXgpXG4gICAgaWYgKG1hdGNoZXMgJiYgbWF0Y2hlcy5sZW5ndGgpIHtcbiAgICAgIG1hdGNoZXMgPSBtYXRjaGVzWzBdLm1hdGNoKGh0bWxBdHRyaWJzUmVnZXgpXG4gICAgICBpZiAobWF0Y2hlcy5sZW5ndGgpIHtcbiAgICAgICAgbWF0Y2hlcy5zaGlmdCgpXG4gICAgICAgIG1hdGNoZXMuZm9yRWFjaChmdW5jdGlvbihodG1sQXR0cmliKSB7XG4gICAgICAgICAgdmFyIGF0dHIgPSBodG1sQXR0cmliLnRyaW0oKS5zcGxpdChcIj1cIilcbiAgICAgICAgICBpZiAoYXR0ci5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICAgIHRtcEVsLmRvY3VtZW50RWxlbWVudC5zZXRBdHRyaWJ1dGUoYXR0clswXSwgdHJ1ZSlcbiAgICAgICAgICB9XG4gICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuc2V0QXR0cmlidXRlKGF0dHJbMF0sIGF0dHJbMV0uc2xpY2UoMSwgLTEpKVxuICAgICAgICAgIH1cbiAgICAgICAgfSlcbiAgICAgIH1cbiAgICB9XG5cbiAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuaW5uZXJIVE1MID0gaHRtbFxuICAgIHRoaXMubG9nKFwibG9hZCBjb250ZW50XCIsIHRtcEVsLmRvY3VtZW50RWxlbWVudC5hdHRyaWJ1dGVzLCB0bXBFbC5kb2N1bWVudEVsZW1lbnQuaW5uZXJIVE1MLmxlbmd0aClcblxuICAgIC8vIENsZWFyIG91dCBhbnkgZm9jdXNlZCBjb250cm9scyBiZWZvcmUgaW5zZXJ0aW5nIG5ldyBwYWdlIGNvbnRlbnRzLlxuICAgIC8vIHdlIGNsZWFyIGZvY3VzIG9uIG5vbiBmb3JtIGVsZW1lbnRzXG4gICAgaWYgKGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQgJiYgIWRvY3VtZW50LmFjdGl2ZUVsZW1lbnQudmFsdWUpIHtcbiAgICAgIHRyeSB7XG4gICAgICAgIGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQuYmx1cigpXG4gICAgICB9IGNhdGNoIChlKSB7IH1cbiAgICB9XG5cbiAgICAvLyB0cnkge1xuICAgIHRoaXMuc3dpdGNoU2VsZWN0b3JzKHRoaXMub3B0aW9ucy5zZWxlY3RvcnMsIHRtcEVsLCBkb2N1bWVudCwgb3B0aW9ucylcblxuICAgIC8vIEZGIGJ1ZzogV29u4oCZdCBhdXRvZm9jdXMgZmllbGRzIHRoYXQgYXJlIGluc2VydGVkIHZpYSBKUy5cbiAgICAvLyBUaGlzIGJlaGF2aW9yIGlzIGluY29ycmVjdC4gU28gaWYgdGhlcmVzIG5vIGN1cnJlbnQgZm9jdXMsIGF1dG9mb2N1c1xuICAgIC8vIHRoZSBsYXN0IGZpZWxkLlxuICAgIC8vXG4gICAgLy8gaHR0cDovL3d3dy53My5vcmcvaHRtbC93Zy9kcmFmdHMvaHRtbC9tYXN0ZXIvZm9ybXMuaHRtbFxuICAgIHZhciBhdXRvZm9jdXNFbCA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoXCJbYXV0b2ZvY3VzXVwiKSkucG9wKClcbiAgICBpZiAoYXV0b2ZvY3VzRWwgJiYgZG9jdW1lbnQuYWN0aXZlRWxlbWVudCAhPT0gYXV0b2ZvY3VzRWwpIHtcbiAgICAgIGF1dG9mb2N1c0VsLmZvY3VzKCk7XG4gICAgfVxuXG4gICAgLy8gZXhlY3V0ZSBzY3JpcHRzIHdoZW4gRE9NIGhhdmUgYmVlbiBjb21wbGV0ZWx5IHVwZGF0ZWRcbiAgICB0aGlzLm9wdGlvbnMuc2VsZWN0b3JzLmZvckVhY2goZnVuY3Rpb24oc2VsZWN0b3IpIHtcbiAgICAgIGZvckVhY2hFbHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvciksIGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgIGV4ZWN1dGVTY3JpcHRzKGVsKVxuICAgICAgfSlcbiAgICB9KVxuICAgIC8vIH1cbiAgICAvLyBjYXRjaChlKSB7XG4gICAgLy8gICBpZiAodGhpcy5vcHRpb25zLmRlYnVnKSB7XG4gICAgLy8gICAgIHRoaXMubG9nKFwiUGpheCBzd2l0Y2ggZmFpbDogXCIsIGUpXG4gICAgLy8gICB9XG4gICAgLy8gICB0aGlzLnN3aXRjaEZhbGxiYWNrKHRtcEVsLCBkb2N1bWVudClcbiAgICAvLyB9XG4gIH0sXG5cbiAgZG9SZXF1ZXN0OiByZXF1aXJlKFwiLi9saWIvcmVxdWVzdC5qc1wiKSxcblxuICBsb2FkVXJsOiBmdW5jdGlvbihocmVmLCBvcHRpb25zKSB7XG4gICAgdGhpcy5sb2coXCJsb2FkIGhyZWZcIiwgaHJlZiwgb3B0aW9ucylcblxuICAgIHRyaWdnZXIoZG9jdW1lbnQsIFwicGpheDpzZW5kXCIsIG9wdGlvbnMpO1xuXG4gICAgLy8gRG8gdGhlIHJlcXVlc3RcbiAgICB0aGlzLmRvUmVxdWVzdChocmVmLCBvcHRpb25zLnJlcXVlc3RPcHRpb25zLCBmdW5jdGlvbihodG1sKSB7XG4gICAgICAvLyBGYWlsIGlmIHVuYWJsZSB0byBsb2FkIEhUTUwgdmlhIEFKQVhcbiAgICAgIGlmIChodG1sID09PSBmYWxzZSkge1xuICAgICAgICB0cmlnZ2VyKGRvY3VtZW50LFwicGpheDpjb21wbGV0ZSBwamF4OmVycm9yXCIsIG9wdGlvbnMpXG5cbiAgICAgICAgcmV0dXJuXG4gICAgICB9XG5cbiAgICAgIC8vIENsZWFyIG91dCBhbnkgZm9jdXNlZCBjb250cm9scyBiZWZvcmUgaW5zZXJ0aW5nIG5ldyBwYWdlIGNvbnRlbnRzLlxuICAgICAgZG9jdW1lbnQuYWN0aXZlRWxlbWVudC5ibHVyKClcblxuICAgICAgdHJ5IHtcbiAgICAgICAgdGhpcy5sb2FkQ29udGVudChodG1sLCBvcHRpb25zKVxuICAgICAgfVxuICAgICAgY2F0Y2ggKGUpIHtcbiAgICAgICAgaWYgKCF0aGlzLm9wdGlvbnMuZGVidWcpIHtcbiAgICAgICAgICBpZiAoY29uc29sZSAmJiBjb25zb2xlLmVycm9yKSB7XG4gICAgICAgICAgICBjb25zb2xlLmVycm9yKFwiUGpheCBzd2l0Y2ggZmFpbDogXCIsIGUpXG4gICAgICAgICAgfVxuICAgICAgICAgIHRoaXMubGF0ZXN0Q2hhbmNlKGhyZWYpXG4gICAgICAgICAgcmV0dXJuXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgdGhyb3cgZVxuICAgICAgICB9XG4gICAgICB9XG5cbiAgICAgIGlmIChvcHRpb25zLmhpc3RvcnkpIHtcbiAgICAgICAgaWYgKHRoaXMuZmlyc3RydW4pIHtcbiAgICAgICAgICB0aGlzLmxhc3RVaWQgPSB0aGlzLm1heFVpZCA9IG5ld1VpZCgpXG4gICAgICAgICAgdGhpcy5maXJzdHJ1biA9IGZhbHNlXG4gICAgICAgICAgd2luZG93Lmhpc3RvcnkucmVwbGFjZVN0YXRlKHtcbiAgICAgICAgICAgIHVybDogd2luZG93LmxvY2F0aW9uLmhyZWYsXG4gICAgICAgICAgICB0aXRsZTogZG9jdW1lbnQudGl0bGUsXG4gICAgICAgICAgICB1aWQ6IHRoaXMubWF4VWlkXG4gICAgICAgICAgfSxcbiAgICAgICAgICBkb2N1bWVudC50aXRsZSlcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIFVwZGF0ZSBicm93c2VyIGhpc3RvcnlcbiAgICAgICAgdGhpcy5sYXN0VWlkID0gdGhpcy5tYXhVaWQgPSBuZXdVaWQoKVxuICAgICAgICB3aW5kb3cuaGlzdG9yeS5wdXNoU3RhdGUoe1xuICAgICAgICAgIHVybDogaHJlZixcbiAgICAgICAgICB0aXRsZTogb3B0aW9ucy50aXRsZSxcbiAgICAgICAgICB1aWQ6IHRoaXMubWF4VWlkXG4gICAgICAgIH0sXG4gICAgICAgICAgb3B0aW9ucy50aXRsZSxcbiAgICAgICAgICBocmVmKVxuICAgICAgfVxuXG4gICAgICB0aGlzLmZvckVhY2hTZWxlY3RvcnMoZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgdGhpcy5wYXJzZURPTShlbClcbiAgICAgIH0sIHRoaXMpXG5cbiAgICAgIC8vIEZpcmUgRXZlbnRzXG4gICAgICB0cmlnZ2VyKGRvY3VtZW50LFwicGpheDpjb21wbGV0ZSBwamF4OnN1Y2Nlc3NcIiwgb3B0aW9ucylcblxuICAgICAgb3B0aW9ucy5hbmFseXRpY3MoKVxuXG4gICAgICAvLyBTY3JvbGwgcGFnZSB0byB0b3Agb24gbmV3IHBhZ2UgbG9hZFxuICAgICAgaWYgKG9wdGlvbnMuc2Nyb2xsVG8gIT09IGZhbHNlKSB7XG4gICAgICAgIGlmIChvcHRpb25zLnNjcm9sbFRvLmxlbmd0aCA+IDEpIHtcbiAgICAgICAgICB3aW5kb3cuc2Nyb2xsVG8ob3B0aW9ucy5zY3JvbGxUb1swXSwgb3B0aW9ucy5zY3JvbGxUb1sxXSlcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICB3aW5kb3cuc2Nyb2xsVG8oMCwgb3B0aW9ucy5zY3JvbGxUbylcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH0uYmluZCh0aGlzKSlcbiAgfVxufVxuXG5QamF4LmlzU3VwcG9ydGVkID0gcmVxdWlyZShcIi4vbGliL2lzLXN1cHBvcnRlZC5qc1wiKTtcblxuLy9hcmd1YWJseSBjb3VsZCBkbyBgaWYoIHJlcXVpcmUoXCIuL2xpYi9pcy1zdXBwb3J0ZWQuanNcIikoKSkge2AgYnV0IHRoYXQgbWlnaHQgYmUgYSBsaXR0bGUgdG8gc2ltcGxlXG5pZiAoUGpheC5pc1N1cHBvcnRlZCgpKSB7XG4gIG1vZHVsZS5leHBvcnRzID0gUGpheFxufVxuLy8gaWYgdGhlcmUgaXNu4oCZdCByZXF1aXJlZCBicm93c2VyIGZ1bmN0aW9ucywgcmV0dXJuaW5nIHN0dXBpZCBhcGlcbmVsc2Uge1xuICB2YXIgc3R1cGlkUGpheCA9IGZ1bmN0aW9uKCkge31cbiAgZm9yICh2YXIga2V5IGluIFBqYXgucHJvdG90eXBlKSB7XG4gICAgaWYgKFBqYXgucHJvdG90eXBlLmhhc093blByb3BlcnR5KGtleSkgJiYgdHlwZW9mIFBqYXgucHJvdG90eXBlW2tleV0gPT09IFwiZnVuY3Rpb25cIikge1xuICAgICAgc3R1cGlkUGpheFtrZXldID0gc3R1cGlkUGpheFxuICAgIH1cbiAgfVxuXG4gIG1vZHVsZS5leHBvcnRzID0gc3R1cGlkUGpheFxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihvYmopIHtcbiAgaWYgKG51bGwgPT09IG9iaiB8fCBcIm9iamVjdFwiICE9IHR5cGVvZiBvYmopIHtcbiAgICByZXR1cm4gb2JqXG4gIH1cbiAgdmFyIGNvcHkgPSBvYmouY29uc3RydWN0b3IoKVxuICBmb3IgKHZhciBhdHRyIGluIG9iaikge1xuICAgIGlmIChvYmouaGFzT3duUHJvcGVydHkoYXR0cikpIHtcbiAgICAgIGNvcHlbYXR0cl0gPSBvYmpbYXR0cl1cbiAgICB9XG4gIH1cbiAgcmV0dXJuIGNvcHlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgLy8gY29uc29sZS5sb2coXCJnb2luZyB0byBleGVjdXRlIHNjcmlwdFwiLCBlbClcblxuICB2YXIgY29kZSA9IChlbC50ZXh0IHx8IGVsLnRleHRDb250ZW50IHx8IGVsLmlubmVySFRNTCB8fCBcIlwiKVxuICB2YXIgcGFyZW50ID0gZWwucGFyZW50Tm9kZSB8fCBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiaGVhZFwiKSB8fCBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnRcbiAgdmFyIHNjcmlwdCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoXCJzY3JpcHRcIilcblxuICBpZiAoY29kZS5tYXRjaChcImRvY3VtZW50LndyaXRlXCIpKSB7XG4gICAgaWYgKGNvbnNvbGUgJiYgY29uc29sZS5sb2cpIHtcbiAgICAgIGNvbnNvbGUubG9nKFwiU2NyaXB0IGNvbnRhaW5zIGRvY3VtZW50LndyaXRlLiBDYW7igJl0IGJlIGV4ZWN1dGVkIGNvcnJlY3RseS4gQ29kZSBza2lwcGVkIFwiLCBlbClcbiAgICB9XG4gICAgcmV0dXJuIGZhbHNlXG4gIH1cblxuICBzY3JpcHQudHlwZSA9IFwidGV4dC9qYXZhc2NyaXB0XCJcbiAgdHJ5IHtcbiAgICBzY3JpcHQuYXBwZW5kQ2hpbGQoZG9jdW1lbnQuY3JlYXRlVGV4dE5vZGUoY29kZSkpXG4gIH1cbiAgY2F0Y2ggKGUpIHtcbiAgICAvLyBvbGQgSUVzIGhhdmUgZnVua3kgc2NyaXB0IG5vZGVzXG4gICAgc2NyaXB0LnRleHQgPSBjb2RlXG4gIH1cblxuICAvLyBleGVjdXRlXG4gIHBhcmVudC5hcHBlbmRDaGlsZChzY3JpcHQpO1xuICAvLyBhdm9pZCBwb2xsdXRpb24gb25seSBpbiBoZWFkIG9yIGJvZHkgdGFnc1xuICBpZiAoW1wiaGVhZFwiLFwiYm9keVwiXS5pbmRleE9mKHBhcmVudC50YWdOYW1lLnRvTG93ZXJDYXNlKCkpID4gMCkge1xuICAgIHBhcmVudC5yZW1vdmVDaGlsZChzY3JpcHQpXG4gIH1cblxuICByZXR1cm4gdHJ1ZVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbHMsIGV2ZW50cywgbGlzdGVuZXIsIHVzZUNhcHR1cmUpIHtcbiAgZXZlbnRzID0gKHR5cGVvZiBldmVudHMgPT09IFwic3RyaW5nXCIgPyBldmVudHMuc3BsaXQoXCIgXCIpIDogZXZlbnRzKVxuXG4gIGV2ZW50cy5mb3JFYWNoKGZ1bmN0aW9uKGUpIHtcbiAgICBmb3JFYWNoRWxzKGVscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIGVsLmFkZEV2ZW50TGlzdGVuZXIoZSwgbGlzdGVuZXIsIHVzZUNhcHR1cmUpXG4gICAgfSlcbiAgfSlcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4uL2ZvcmVhY2gtZWxzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxzLCBldmVudHMsIG9wdHMpIHtcbiAgZXZlbnRzID0gKHR5cGVvZiBldmVudHMgPT09IFwic3RyaW5nXCIgPyBldmVudHMuc3BsaXQoXCIgXCIpIDogZXZlbnRzKVxuXG4gIGV2ZW50cy5mb3JFYWNoKGZ1bmN0aW9uKGUpIHtcbiAgICB2YXIgZXZlbnQgLy8gPSBuZXcgQ3VzdG9tRXZlbnQoZSkgLy8gZG9lc24ndCBldmVyeXdoZXJlIHlldFxuICAgIGV2ZW50ID0gZG9jdW1lbnQuY3JlYXRlRXZlbnQoXCJIVE1MRXZlbnRzXCIpXG4gICAgZXZlbnQuaW5pdEV2ZW50KGUsIHRydWUsIHRydWUpXG4gICAgZXZlbnQuZXZlbnROYW1lID0gZVxuICAgIGlmIChvcHRzKSB7XG4gICAgICBPYmplY3Qua2V5cyhvcHRzKS5mb3JFYWNoKGZ1bmN0aW9uKGtleSkge1xuICAgICAgICBldmVudFtrZXldID0gb3B0c1trZXldXG4gICAgICB9KVxuICAgIH1cblxuICAgIGZvckVhY2hFbHMoZWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgdmFyIGRvbUZpeCA9IGZhbHNlXG4gICAgICBpZiAoIWVsLnBhcmVudE5vZGUgJiYgZWwgIT09IGRvY3VtZW50ICYmIGVsICE9PSB3aW5kb3cpIHtcbiAgICAgICAgLy8gVEhBTktTIFlPVSBJRSAoOS8xMC8vMTEgY29uY2VybmVkKVxuICAgICAgICAvLyBkaXNwYXRjaEV2ZW50IGRvZXNuJ3Qgd29yayBpZiBlbGVtZW50IGlzIG5vdCBpbiB0aGUgZG9tXG4gICAgICAgIGRvbUZpeCA9IHRydWVcbiAgICAgICAgZG9jdW1lbnQuYm9keS5hcHBlbmRDaGlsZChlbClcbiAgICAgIH1cbiAgICAgIGVsLmRpc3BhdGNoRXZlbnQoZXZlbnQpXG4gICAgICBpZiAoZG9tRml4KSB7XG4gICAgICAgIGVsLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoZWwpXG4gICAgICB9XG4gICAgfSlcbiAgfSlcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcbnZhciBldmFsU2NyaXB0ID0gcmVxdWlyZShcIi4vZXZhbC1zY3JpcHRcIilcbi8vIEZpbmRzIGFuZCBleGVjdXRlcyBzY3JpcHRzICh1c2VkIGZvciBuZXdseSBhZGRlZCBlbGVtZW50cylcbi8vIE5lZWRlZCBzaW5jZSBpbm5lckhUTUwgZG9lcyBub3QgcnVuIHNjcmlwdHNcbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgLy8gY29uc29sZS5sb2coXCJnb2luZyB0byBleGVjdXRlIHNjcmlwdHMgZm9yIFwiLCBlbClcblxuICBpZiAoZWwudGFnTmFtZS50b0xvd2VyQ2FzZSgpID09PSBcInNjcmlwdFwiKSB7XG4gICAgZXZhbFNjcmlwdChlbCk7XG4gIH1cblxuICBmb3JFYWNoRWxzKGVsLnF1ZXJ5U2VsZWN0b3JBbGwoXCJzY3JpcHRcIiksIGZ1bmN0aW9uKHNjcmlwdCkge1xuICAgIGlmICghc2NyaXB0LnR5cGUgfHwgc2NyaXB0LnR5cGUudG9Mb3dlckNhc2UoKSA9PT0gXCJ0ZXh0L2phdmFzY3JpcHRcIikge1xuICAgICAgaWYgKHNjcmlwdC5wYXJlbnROb2RlKSB7XG4gICAgICAgIHNjcmlwdC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKHNjcmlwdClcbiAgICAgIH1cbiAgICAgIGV2YWxTY3JpcHQoc2NyaXB0KVxuICAgIH1cbiAgfSlcbn1cbiIsIi8qIGdsb2JhbCBIVE1MQ29sbGVjdGlvbjogdHJ1ZSAqL1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVscywgZm4sIGNvbnRleHQpIHtcbiAgaWYgKGVscyBpbnN0YW5jZW9mIEhUTUxDb2xsZWN0aW9uIHx8IGVscyBpbnN0YW5jZW9mIE5vZGVMaXN0IHx8IGVscyBpbnN0YW5jZW9mIEFycmF5KSB7XG4gICAgcmV0dXJuIEFycmF5LnByb3RvdHlwZS5mb3JFYWNoLmNhbGwoZWxzLCBmbiwgY29udGV4dClcbiAgfVxuICAvLyBhc3N1bWUgc2ltcGxlIGRvbSBlbGVtZW50XG4gIHJldHVybiBmbi5jYWxsKGNvbnRleHQsIGVscylcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihzZWxlY3RvcnMsIGNiLCBjb250ZXh0LCBET01jb250ZXh0KSB7XG4gIERPTWNvbnRleHQgPSBET01jb250ZXh0IHx8IGRvY3VtZW50XG4gIHNlbGVjdG9ycy5mb3JFYWNoKGZ1bmN0aW9uKHNlbGVjdG9yKSB7XG4gICAgZm9yRWFjaEVscyhET01jb250ZXh0LnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpLCBjYiwgY29udGV4dClcbiAgfSlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oKSB7XG4gIC8vIEJvcnJvd2VkIHdob2xlc2FsZSBmcm9tIGh0dHBzOi8vZ2l0aHViLmNvbS9kZWZ1bmt0L2pxdWVyeS1wamF4XG4gIHJldHVybiB3aW5kb3cuaGlzdG9yeSAmJlxuICAgIHdpbmRvdy5oaXN0b3J5LnB1c2hTdGF0ZSAmJlxuICAgIHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSAmJlxuICAgIC8vIHB1c2hTdGF0ZSBpc27igJl0IHJlbGlhYmxlIG9uIGlPUyB1bnRpbCA1LlxuICAgICFuYXZpZ2F0b3IudXNlckFnZW50Lm1hdGNoKC8oKGlQb2R8aVBob25lfGlQYWQpLitcXGJPU1xccytbMS00XVxcRHxXZWJBcHBzXFwvLitDRk5ldHdvcmspLylcbn1cbiIsImlmICghRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQpIHtcbiAgRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQgPSBmdW5jdGlvbihvVGhpcykge1xuICAgIGlmICh0eXBlb2YgdGhpcyAhPT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICAvLyBjbG9zZXN0IHRoaW5nIHBvc3NpYmxlIHRvIHRoZSBFQ01BU2NyaXB0IDUgaW50ZXJuYWwgSXNDYWxsYWJsZSBmdW5jdGlvblxuICAgICAgdGhyb3cgbmV3IFR5cGVFcnJvcihcIkZ1bmN0aW9uLnByb3RvdHlwZS5iaW5kIC0gd2hhdCBpcyB0cnlpbmcgdG8gYmUgYm91bmQgaXMgbm90IGNhbGxhYmxlXCIpXG4gICAgfVxuXG4gICAgdmFyIGFBcmdzID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXJndW1lbnRzLCAxKVxuICAgIHZhciB0aGF0ID0gdGhpc1xuICAgIHZhciBGbm9vcCA9IGZ1bmN0aW9uKCkge31cbiAgICB2YXIgZkJvdW5kID0gZnVuY3Rpb24oKSB7XG4gICAgICByZXR1cm4gdGhhdC5hcHBseSh0aGlzIGluc3RhbmNlb2YgRm5vb3AgJiYgb1RoaXMgPyB0aGlzIDogb1RoaXMsIGFBcmdzLmNvbmNhdChBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMpKSlcbiAgICB9XG5cbiAgICBGbm9vcC5wcm90b3R5cGUgPSB0aGlzLnByb3RvdHlwZVxuICAgIGZCb3VuZC5wcm90b3R5cGUgPSBuZXcgRm5vb3AoKVxuXG4gICAgcmV0dXJuIGZCb3VuZFxuICB9XG59XG4iLCJyZXF1aXJlKFwiLi4vcG9seWZpbGxzL0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuLi9ldmVudHMvb25cIilcbnZhciBjbG9uZSA9IHJlcXVpcmUoXCIuLi9jbG9uZVwiKVxuXG52YXIgYXR0ckNsaWNrID0gXCJkYXRhLXBqYXgtY2xpY2stc3RhdGVcIlxudmFyIGF0dHJLZXkgPSBcImRhdGEtcGpheC1rZXl1cC1zdGF0ZVwiXG5cbnZhciBmb3JtQWN0aW9uID0gZnVuY3Rpb24oZWwsIGV2ZW50KXtcblxuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgPSB7XG4gICAgcmVxdWVzdFVybCA6IGVsLmdldEF0dHJpYnV0ZSgnYWN0aW9uJykgfHwgd2luZG93LmxvY2F0aW9uLmhyZWYsXG4gICAgcmVxdWVzdE1ldGhvZCA6IGVsLmdldEF0dHJpYnV0ZSgnbWV0aG9kJykgfHwgJ0dFVCcsXG4gIH1cblxuICAvL2NyZWF0ZSBhIHRlc3RhYmxlIHZpcnR1YWwgbGluayBvZiB0aGUgZm9ybSBhY3Rpb25cbiAgdmFyIHZpcnRMaW5rRWxlbWVudCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2EnKTtcbiAgdmlydExpbmtFbGVtZW50LnNldEF0dHJpYnV0ZSgnaHJlZicsIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0VXJsKTtcblxuICAvLyBJZ25vcmUgZXh0ZXJuYWwgbGlua3MuXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQucHJvdG9jb2wgIT09IHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCB8fCB2aXJ0TGlua0VsZW1lbnQuaG9zdCAhPT0gd2luZG93LmxvY2F0aW9uLmhvc3QpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImV4dGVybmFsXCIpO1xuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGNsaWNrIGlmIHdlIGFyZSBvbiBhbiBhbmNob3Igb24gdGhlIHNhbWUgcGFnZVxuICBpZiAodmlydExpbmtFbGVtZW50LnBhdGhuYW1lID09PSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgJiYgdmlydExpbmtFbGVtZW50Lmhhc2gubGVuZ3RoID4gMCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLXByZXNlbnRcIik7XG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBpZiBkZWNsYXJlZCBhcyBhIGZ1bGwgcmVsb2FkLCBqdXN0IG5vcm1hbGx5IHN1Ym1pdCB0aGUgZm9ybVxuICBpZiAoIHRoaXMub3B0aW9ucy5jdXJyZW50VXJsRnVsbFJlbG9hZCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwicmVsb2FkXCIpO1xuICAgIHJldHVybjtcbiAgfVxuXG4gIGV2ZW50LnByZXZlbnREZWZhdWx0KClcblxuICB2YXIgcGFyYW1PYmplY3QgPSBbXTtcbiAgZm9yKHZhciBlbGVtZW50S2V5IGluIGVsLmVsZW1lbnRzKSB7XG4gICAgdmFyIGVsZW1lbnQgPSBlbC5lbGVtZW50c1tlbGVtZW50S2V5XTtcbiAgICBpZiAoISFlbGVtZW50Lm5hbWUgJiYgZWxlbWVudC5hdHRyaWJ1dGVzICE9PSB1bmRlZmluZWQgJiYgZWxlbWVudC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgIT09ICdidXR0b24nKXtcbiAgICAgIGlmICgoZWxlbWVudC5hdHRyaWJ1dGVzLnR5cGUgIT09ICdjaGVja2JveCcgJiYgZWxlbWVudC5hdHRyaWJ1dGVzLnR5cGUgIT09ICdyYWRpbycpIHx8IGVsZW1lbnQuY2hlY2tlZCkge1xuICAgICAgICBwYXJhbU9iamVjdC5wdXNoKHsgbmFtZTogZW5jb2RlVVJJQ29tcG9uZW50KGVsZW1lbnQubmFtZSksIHZhbHVlOiBlbmNvZGVVUklDb21wb25lbnQoZWxlbWVudC52YWx1ZSl9KTtcbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICAvL0NyZWF0aW5nIGEgZ2V0U3RyaW5nXG4gIHZhciBwYXJhbXNTdHJpbmcgPSAocGFyYW1PYmplY3QubWFwKGZ1bmN0aW9uKHZhbHVlKXtyZXR1cm4gdmFsdWUubmFtZStcIj1cIit2YWx1ZS52YWx1ZTt9KSkuam9pbignJicpO1xuXG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0UGF5bG9hZCA9IHBhcmFtT2JqZWN0O1xuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgPSBwYXJhbXNTdHJpbmc7XG5cbiAgdGhpcy5sb2FkVXJsKHZpcnRMaW5rRWxlbWVudC5ocmVmLCBjbG9uZSh0aGlzLm9wdGlvbnMpKVxuXG59O1xuXG52YXIgaXNEZWZhdWx0UHJldmVudGVkID0gZnVuY3Rpb24oZXZlbnQpIHtcbiAgcmV0dXJuIGV2ZW50LmRlZmF1bHRQcmV2ZW50ZWQgfHwgZXZlbnQucmV0dXJuVmFsdWUgPT09IGZhbHNlO1xufTtcblxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciB0aGF0ID0gdGhpc1xuXG4gIG9uKGVsLCBcInN1Ym1pdFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBmb3JtQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICB9KVxuXG4gIG9uKGVsLCBcImtleXVwXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIC8vIERvbuKAmXQgYnJlYWsgYnJvd3NlciBzcGVjaWFsIGJlaGF2aW9yIG9uIGxpbmtzIChsaWtlIHBhZ2UgaW4gbmV3IHdpbmRvdylcbiAgICBpZiAoZXZlbnQud2hpY2ggPiAxIHx8IGV2ZW50Lm1ldGFLZXkgfHwgZXZlbnQuY3RybEtleSB8fCBldmVudC5zaGlmdEtleSB8fCBldmVudC5hbHRLZXkpIHtcbiAgICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyS2V5LCBcIm1vZGlmaWVyXCIpXG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBpZiAoZXZlbnQua2V5Q29kZSA9PSAxMykge1xuICAgICAgZm9ybUFjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgICB9XG4gIH0uYmluZCh0aGlzKSlcbn1cbiIsInJlcXVpcmUoXCIuLi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmRcIilcblxudmFyIG9uID0gcmVxdWlyZShcIi4uL2V2ZW50cy9vblwiKVxudmFyIGNsb25lID0gcmVxdWlyZShcIi4uL2Nsb25lXCIpXG5cbnZhciBhdHRyQ2xpY2sgPSBcImRhdGEtcGpheC1jbGljay1zdGF0ZVwiXG52YXIgYXR0cktleSA9IFwiZGF0YS1wamF4LWtleXVwLXN0YXRlXCJcblxudmFyIGxpbmtBY3Rpb24gPSBmdW5jdGlvbihlbCwgZXZlbnQpIHtcbiAgLy8gRG9u4oCZdCBicmVhayBicm93c2VyIHNwZWNpYWwgYmVoYXZpb3Igb24gbGlua3MgKGxpa2UgcGFnZSBpbiBuZXcgd2luZG93KVxuICBpZiAoZXZlbnQud2hpY2ggPiAxIHx8IGV2ZW50Lm1ldGFLZXkgfHwgZXZlbnQuY3RybEtleSB8fCBldmVudC5zaGlmdEtleSB8fCBldmVudC5hbHRLZXkpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcIm1vZGlmaWVyXCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyB3ZSBkbyB0ZXN0IG9uIGhyZWYgbm93IHRvIHByZXZlbnQgdW5leHBlY3RlZCBiZWhhdmlvciBpZiBmb3Igc29tZSByZWFzb25cbiAgLy8gdXNlciBoYXZlIGhyZWYgdGhhdCBjYW4gYmUgZHluYW1pY2FsbHkgdXBkYXRlZFxuXG4gIC8vIElnbm9yZSBleHRlcm5hbCBsaW5rcy5cbiAgaWYgKGVsLnByb3RvY29sICE9PSB3aW5kb3cubG9jYXRpb24ucHJvdG9jb2wgfHwgZWwuaG9zdCAhPT0gd2luZG93LmxvY2F0aW9uLmhvc3QpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImV4dGVybmFsXCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgY2xpY2sgaWYgd2UgYXJlIG9uIGFuIGFuY2hvciBvbiB0aGUgc2FtZSBwYWdlXG4gIGlmIChlbC5wYXRobmFtZSA9PT0gd2luZG93LmxvY2F0aW9uLnBhdGhuYW1lICYmIGVsLmhhc2gubGVuZ3RoID4gMCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLXByZXNlbnRcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBhbmNob3JzIG9uIHRoZSBzYW1lIHBhZ2UgKGtlZXAgbmF0aXZlIGJlaGF2aW9yKVxuICBpZiAoZWwuaGFzaCAmJiBlbC5ocmVmLnJlcGxhY2UoZWwuaGFzaCwgXCJcIikgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnJlcGxhY2UobG9jYXRpb24uaGFzaCwgXCJcIikpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvclwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGVtcHR5IGFuY2hvciBcImZvby5odG1sI1wiXG4gIGlmIChlbC5ocmVmID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5zcGxpdChcIiNcIilbMF0gKyBcIiNcIikge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLWVtcHR5XCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICBldmVudC5wcmV2ZW50RGVmYXVsdCgpXG5cbiAgLy8gZG9u4oCZdCBkbyBcIm5vdGhpbmdcIiBpZiB1c2VyIHRyeSB0byByZWxvYWQgdGhlIHBhZ2UgYnkgY2xpY2tpbmcgdGhlIHNhbWUgbGluayB0d2ljZVxuICBpZiAoXG4gICAgdGhpcy5vcHRpb25zLmN1cnJlbnRVcmxGdWxsUmVsb2FkICYmXG4gICAgZWwuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdXG4gICkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwicmVsb2FkXCIpXG4gICAgdGhpcy5yZWxvYWQoKVxuICAgIHJldHVyblxuICB9XG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucyA9IHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucyB8fCB7fTtcbiAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJsb2FkXCIpXG4gIHRoaXMubG9hZFVybChlbC5ocmVmLCBjbG9uZSh0aGlzLm9wdGlvbnMpKVxufVxuXG52YXIgaXNEZWZhdWx0UHJldmVudGVkID0gZnVuY3Rpb24oZXZlbnQpIHtcbiAgcmV0dXJuIGV2ZW50LmRlZmF1bHRQcmV2ZW50ZWQgfHwgZXZlbnQucmV0dXJuVmFsdWUgPT09IGZhbHNlO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciB0aGF0ID0gdGhpc1xuXG4gIG9uKGVsLCBcImNsaWNrXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIGxpbmtBY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gIH0pXG5cbiAgb24oZWwsIFwia2V5dXBcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgLy8gRG9u4oCZdCBicmVhayBicm93c2VyIHNwZWNpYWwgYmVoYXZpb3Igb24gbGlua3MgKGxpa2UgcGFnZSBpbiBuZXcgd2luZG93KVxuICAgIGlmIChldmVudC53aGljaCA+IDEgfHwgZXZlbnQubWV0YUtleSB8fCBldmVudC5jdHJsS2V5IHx8IGV2ZW50LnNoaWZ0S2V5IHx8IGV2ZW50LmFsdEtleSkge1xuICAgICAgZWwuc2V0QXR0cmlidXRlKGF0dHJLZXksIFwibW9kaWZpZXJcIilcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIGlmIChldmVudC5rZXlDb2RlID09IDEzKSB7XG4gICAgICBsaW5rQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICAgIH1cbiAgfS5iaW5kKHRoaXMpKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICByZXR1cm4gZWwucXVlcnlTZWxlY3RvckFsbCh0aGlzLm9wdGlvbnMuZWxlbWVudHMpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKCkge1xuICBpZiAodGhpcy5vcHRpb25zLmRlYnVnICYmIGNvbnNvbGUpIHtcbiAgICBpZiAodHlwZW9mIGNvbnNvbGUubG9nID09PSBcImZ1bmN0aW9uXCIpIHtcbiAgICAgIGNvbnNvbGUubG9nLmFwcGx5KGNvbnNvbGUsIGFyZ3VtZW50cyk7XG4gICAgfVxuICAgIC8vIGllIGlzIHdlaXJkXG4gICAgZWxzZSBpZiAoY29uc29sZS5sb2cpIHtcbiAgICAgIGNvbnNvbGUubG9nKGFyZ3VtZW50cyk7XG4gICAgfVxuICB9XG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG52YXIgcGFyc2VFbGVtZW50ID0gcmVxdWlyZShcIi4vcGFyc2UtZWxlbWVudFwiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIGZvckVhY2hFbHModGhpcy5nZXRFbGVtZW50cyhlbCksIHBhcnNlRWxlbWVudCwgdGhpcylcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgc3dpdGNoIChlbC50YWdOYW1lLnRvTG93ZXJDYXNlKCkpIHtcbiAgY2FzZSBcImFcIjpcbiAgICAvLyBvbmx5IGF0dGFjaCBsaW5rIGlmIGVsIGRvZXMgbm90IGFscmVhZHkgaGF2ZSBsaW5rIGF0dGFjaGVkXG4gICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICB0aGlzLmF0dGFjaExpbmsoZWwpXG4gICAgfVxuICAgIGJyZWFrXG5cbiAgICBjYXNlIFwiZm9ybVwiOlxuICAgICAgLy8gb25seSBhdHRhY2ggbGluayBpZiBlbCBkb2VzIG5vdCBhbHJlYWR5IGhhdmUgbGluayBhdHRhY2hlZFxuICAgICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICAgIHRoaXMuYXR0YWNoRm9ybShlbClcbiAgICAgIH1cbiAgICBicmVha1xuXG4gIGRlZmF1bHQ6XG4gICAgdGhyb3cgXCJQamF4IGNhbiBvbmx5IGJlIGFwcGxpZWQgb24gPGE+IG9yIDxmb3JtPiBzdWJtaXRcIlxuICB9XG59XG4iLCIvKiBnbG9iYWwgX2dhcTogdHJ1ZSwgZ2E6IHRydWUgKi9cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihvcHRpb25zKXtcbiAgdGhpcy5vcHRpb25zID0gb3B0aW9uc1xuICB0aGlzLm9wdGlvbnMuZWxlbWVudHMgPSB0aGlzLm9wdGlvbnMuZWxlbWVudHMgfHwgXCJhW2hyZWZdLCBmb3JtW2FjdGlvbl1cIlxuICB0aGlzLm9wdGlvbnMuc2VsZWN0b3JzID0gdGhpcy5vcHRpb25zLnNlbGVjdG9ycyB8fCBbXCJ0aXRsZVwiLCBcIi5qcy1QamF4XCJdXG4gIHRoaXMub3B0aW9ucy5zd2l0Y2hlcyA9IHRoaXMub3B0aW9ucy5zd2l0Y2hlcyB8fCB7fVxuICB0aGlzLm9wdGlvbnMuc3dpdGNoZXNPcHRpb25zID0gdGhpcy5vcHRpb25zLnN3aXRjaGVzT3B0aW9ucyB8fCB7fVxuICB0aGlzLm9wdGlvbnMuaGlzdG9yeSA9IHRoaXMub3B0aW9ucy5oaXN0b3J5IHx8IHRydWVcbiAgdGhpcy5vcHRpb25zLmFuYWx5dGljcyA9IHRoaXMub3B0aW9ucy5hbmFseXRpY3MgfHwgZnVuY3Rpb24oKSB7XG4gICAgLy8gb3B0aW9ucy5iYWNrd2FyZCBvciBvcHRpb25zLmZvd2FyZCBjYW4gYmUgdHJ1ZSBvciB1bmRlZmluZWRcbiAgICAvLyBieSBkZWZhdWx0LCB3ZSBkbyB0cmFjayBiYWNrL2Zvd2FyZCBoaXRcbiAgICAvLyBodHRwczovL3Byb2R1Y3Rmb3J1bXMuZ29vZ2xlLmNvbS9mb3J1bS8jIXRvcGljL2FuYWx5dGljcy9XVndNRGpMaFhZa1xuICAgIGlmICh3aW5kb3cuX2dhcSkge1xuICAgICAgX2dhcS5wdXNoKFtcIl90cmFja1BhZ2V2aWV3XCJdKVxuICAgIH1cbiAgICBpZiAod2luZG93LmdhKSB7XG4gICAgICBnYShcInNlbmRcIiwgXCJwYWdldmlld1wiLCB7cGFnZTogbG9jYXRpb24ucGF0aG5hbWUsIHRpdGxlOiBkb2N1bWVudC50aXRsZX0pXG4gICAgfVxuICB9XG4gIHRoaXMub3B0aW9ucy5zY3JvbGxUbyA9ICh0eXBlb2YgdGhpcy5vcHRpb25zLnNjcm9sbFRvID09PSAndW5kZWZpbmVkJykgPyAwIDogdGhpcy5vcHRpb25zLnNjcm9sbFRvO1xuICB0aGlzLm9wdGlvbnMuY2FjaGVCdXN0ID0gKHR5cGVvZiB0aGlzLm9wdGlvbnMuY2FjaGVCdXN0ID09PSAndW5kZWZpbmVkJykgPyB0cnVlIDogdGhpcy5vcHRpb25zLmNhY2hlQnVzdFxuICB0aGlzLm9wdGlvbnMuZGVidWcgPSB0aGlzLm9wdGlvbnMuZGVidWcgfHwgZmFsc2VcblxuICAvLyB3ZSBjYW7igJl0IHJlcGxhY2UgYm9keS5vdXRlckhUTUwgb3IgaGVhZC5vdXRlckhUTUxcbiAgLy8gaXQgY3JlYXRlIGEgYnVnIHdoZXJlIG5ldyBib2R5IG9yIG5ldyBoZWFkIGFyZSBjcmVhdGVkIGluIHRoZSBkb21cbiAgLy8gaWYgeW91IHNldCBoZWFkLm91dGVySFRNTCwgYSBuZXcgYm9keSB0YWcgaXMgYXBwZW5kZWQsIHNvIHRoZSBkb20gZ2V0IDIgYm9keVxuICAvLyAmIGl0IGJyZWFrIHRoZSBzd2l0Y2hGYWxsYmFjayB3aGljaCByZXBsYWNlIGhlYWQgJiBib2R5XG4gIGlmICghdGhpcy5vcHRpb25zLnN3aXRjaGVzLmhlYWQpIHtcbiAgICB0aGlzLm9wdGlvbnMuc3dpdGNoZXMuaGVhZCA9IHRoaXMuc3dpdGNoRWxlbWVudHNBbHRcbiAgfVxuICBpZiAoIXRoaXMub3B0aW9ucy5zd2l0Y2hlcy5ib2R5KSB7XG4gICAgdGhpcy5vcHRpb25zLnN3aXRjaGVzLmJvZHkgPSB0aGlzLnN3aXRjaEVsZW1lbnRzQWx0XG4gIH1cbiAgaWYgKHR5cGVvZiBvcHRpb25zLmFuYWx5dGljcyAhPT0gXCJmdW5jdGlvblwiKSB7XG4gICAgb3B0aW9ucy5hbmFseXRpY3MgPSBmdW5jdGlvbigpIHt9XG4gIH1cbn0iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHRoaXMucGFyc2VET00oZWwgfHwgZG9jdW1lbnQpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKCkge1xuICB3aW5kb3cubG9jYXRpb24ucmVsb2FkKClcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24obG9jYXRpb24sIG9wdGlvbnMsIGNhbGxiYWNrKSB7XG4gIG9wdGlvbnMgPSBvcHRpb25zIHx8IHt9O1xuICB2YXIgcmVxdWVzdE1ldGhvZCA9IG9wdGlvbnMucmVxdWVzdE1ldGhvZCB8fCBcIkdFVFwiO1xuICB2YXIgcmVxdWVzdFBheWxvYWQgPSBvcHRpb25zLnJlcXVlc3RQYXlsb2FkU3RyaW5nIHx8IG51bGw7XG4gIHZhciByZXF1ZXN0ID0gbmV3IFhNTEh0dHBSZXF1ZXN0KClcblxuICByZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9IGZ1bmN0aW9uKCkge1xuICAgIGlmIChyZXF1ZXN0LnJlYWR5U3RhdGUgPT09IDQpIHtcbiAgICAgIGlmIChyZXF1ZXN0LnN0YXR1cyA9PT0gMjAwKSB7XG4gICAgICAgIGNhbGxiYWNrKHJlcXVlc3QucmVzcG9uc2VUZXh0LCByZXF1ZXN0KVxuICAgICAgfVxuICAgICAgZWxzZSB7XG4gICAgICAgIGNhbGxiYWNrKG51bGwsIHJlcXVlc3QpXG4gICAgICB9XG4gICAgfVxuICB9XG5cbiAgLy8gQWRkIGEgdGltZXN0YW1wIGFzIHBhcnQgb2YgdGhlIHF1ZXJ5IHN0cmluZyBpZiBjYWNoZSBidXN0aW5nIGlzIGVuYWJsZWRcbiAgaWYgKHRoaXMub3B0aW9ucy5jYWNoZUJ1c3QpIHtcbiAgICBsb2NhdGlvbiArPSAoIS9bPyZdLy50ZXN0KGxvY2F0aW9uKSA/IFwiP1wiIDogXCImXCIpICsgbmV3IERhdGUoKS5nZXRUaW1lKClcbiAgfVxuXG4gIHJlcXVlc3Qub3BlbihyZXF1ZXN0TWV0aG9kLnRvVXBwZXJDYXNlKCksIGxvY2F0aW9uLCB0cnVlKVxuICByZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoXCJYLVJlcXVlc3RlZC1XaXRoXCIsIFwiWE1MSHR0cFJlcXVlc3RcIilcblxuICAvLyBBZGQgdGhlIHJlcXVlc3QgcGF5bG9hZCBpZiBhdmFpbGFibGVcbiAgaWYgKG9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgIT0gdW5kZWZpbmVkICYmIG9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgIT0gXCJcIikge1xuICAgIC8vIFNlbmQgdGhlIHByb3BlciBoZWFkZXIgaW5mb3JtYXRpb24gYWxvbmcgd2l0aCB0aGUgcmVxdWVzdFxuICAgIHJlcXVlc3Quc2V0UmVxdWVzdEhlYWRlcihcIkNvbnRlbnQtdHlwZVwiLCBcImFwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZFwiKTtcbiAgfVxuXG4gIHJlcXVlc3Quc2VuZChyZXF1ZXN0UGF5bG9hZClcblxuICByZXR1cm4gcmVxdWVzdFxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9mb3JlYWNoLWVsc1wiKVxuXG52YXIgZGVmYXVsdFN3aXRjaGVzID0gcmVxdWlyZShcIi4vc3dpdGNoZXNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihzd2l0Y2hlcywgc3dpdGNoZXNPcHRpb25zLCBzZWxlY3RvcnMsIGZyb21FbCwgdG9FbCwgb3B0aW9ucykge1xuICBzZWxlY3RvcnMuZm9yRWFjaChmdW5jdGlvbihzZWxlY3Rvcikge1xuICAgIHZhciBuZXdFbHMgPSBmcm9tRWwucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcilcbiAgICB2YXIgb2xkRWxzID0gdG9FbC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKVxuICAgIGlmICh0aGlzLmxvZykge1xuICAgICAgdGhpcy5sb2coXCJQamF4IHN3aXRjaFwiLCBzZWxlY3RvciwgbmV3RWxzLCBvbGRFbHMpXG4gICAgfVxuICAgIGlmIChuZXdFbHMubGVuZ3RoICE9PSBvbGRFbHMubGVuZ3RoKSB7XG4gICAgICAvLyBmb3JFYWNoRWxzKG5ld0VscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIC8vICAgdGhpcy5sb2coXCJuZXdFbFwiLCBlbCwgZWwub3V0ZXJIVE1MKVxuICAgICAgLy8gfSwgdGhpcylcbiAgICAgIC8vIGZvckVhY2hFbHMob2xkRWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgLy8gICB0aGlzLmxvZyhcIm9sZEVsXCIsIGVsLCBlbC5vdXRlckhUTUwpXG4gICAgICAvLyB9LCB0aGlzKVxuICAgICAgdGhyb3cgXCJET00gZG9lc27igJl0IGxvb2sgdGhlIHNhbWUgb24gbmV3IGxvYWRlZCBwYWdlOiDigJlcIiArIHNlbGVjdG9yICsgXCLigJkgLSBuZXcgXCIgKyBuZXdFbHMubGVuZ3RoICsgXCIsIG9sZCBcIiArIG9sZEVscy5sZW5ndGhcbiAgICB9XG5cbiAgICBmb3JFYWNoRWxzKG5ld0VscywgZnVuY3Rpb24obmV3RWwsIGkpIHtcbiAgICAgIHZhciBvbGRFbCA9IG9sZEVsc1tpXVxuICAgICAgaWYgKHRoaXMubG9nKSB7XG4gICAgICAgIHRoaXMubG9nKFwibmV3RWxcIiwgbmV3RWwsIFwib2xkRWxcIiwgb2xkRWwpXG4gICAgICB9XG4gICAgICBpZiAoc3dpdGNoZXNbc2VsZWN0b3JdKSB7XG4gICAgICAgIHN3aXRjaGVzW3NlbGVjdG9yXS5iaW5kKHRoaXMpKG9sZEVsLCBuZXdFbCwgb3B0aW9ucywgc3dpdGNoZXNPcHRpb25zW3NlbGVjdG9yXSlcbiAgICAgIH1cbiAgICAgIGVsc2Uge1xuICAgICAgICBkZWZhdWx0U3dpdGNoZXMub3V0ZXJIVE1MLmJpbmQodGhpcykob2xkRWwsIG5ld0VsLCBvcHRpb25zKVxuICAgICAgfVxuICAgIH0sIHRoaXMpXG4gIH0sIHRoaXMpXG59XG4iLCJ2YXIgb24gPSByZXF1aXJlKFwiLi9ldmVudHMvb24uanNcIilcbi8vIHZhciBvZmYgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL29uLmpzXCIpXG4vLyB2YXIgdHJpZ2dlciA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvdHJpZ2dlci5qc1wiKVxuXG5cbm1vZHVsZS5leHBvcnRzID0ge1xuICBvdXRlckhUTUw6IGZ1bmN0aW9uKG9sZEVsLCBuZXdFbCkge1xuICAgIG9sZEVsLm91dGVySFRNTCA9IG5ld0VsLm91dGVySFRNTFxuICAgIHRoaXMub25Td2l0Y2goKVxuICB9LFxuXG4gIGlubmVySFRNTDogZnVuY3Rpb24ob2xkRWwsIG5ld0VsKSB7XG4gICAgb2xkRWwuaW5uZXJIVE1MID0gbmV3RWwuaW5uZXJIVE1MXG4gICAgb2xkRWwuY2xhc3NOYW1lID0gbmV3RWwuY2xhc3NOYW1lXG4gICAgdGhpcy5vblN3aXRjaCgpXG4gIH0sXG5cbiAgc2lkZUJ5U2lkZTogZnVuY3Rpb24ob2xkRWwsIG5ld0VsLCBvcHRpb25zLCBzd2l0Y2hPcHRpb25zKSB7XG4gICAgdmFyIGZvckVhY2ggPSBBcnJheS5wcm90b3R5cGUuZm9yRWFjaFxuICAgIHZhciBlbHNUb1JlbW92ZSA9IFtdXG4gICAgdmFyIGVsc1RvQWRkID0gW11cbiAgICB2YXIgZnJhZ1RvQXBwZW5kID0gZG9jdW1lbnQuY3JlYXRlRG9jdW1lbnRGcmFnbWVudCgpXG4gICAgLy8gaGVpZ2h0IHRyYW5zaXRpb24gYXJlIHNoaXR0eSBvbiBzYWZhcmlcbiAgICAvLyBzbyBjb21tZW50ZWQgZm9yIG5vdyAodW50aWwgSSBmb3VuZCBzb21ldGhpbmcgPylcbiAgICAvLyB2YXIgcmVsZXZhbnRIZWlnaHQgPSAwXG4gICAgdmFyIGFuaW1hdGlvbkV2ZW50TmFtZXMgPSBcImFuaW1hdGlvbmVuZCB3ZWJraXRBbmltYXRpb25FbmQgTVNBbmltYXRpb25FbmQgb2FuaW1hdGlvbmVuZFwiXG4gICAgdmFyIGFuaW1hdGVkRWxzTnVtYmVyID0gMFxuICAgIHZhciBzZXh5QW5pbWF0aW9uRW5kID0gZnVuY3Rpb24oZSkge1xuICAgICAgICAgIGlmIChlLnRhcmdldCAhPSBlLmN1cnJlbnRUYXJnZXQpIHtcbiAgICAgICAgICAgIC8vIGVuZCB0cmlnZ2VyZWQgYnkgYW4gYW5pbWF0aW9uIG9uIGEgY2hpbGRcbiAgICAgICAgICAgIHJldHVyblxuICAgICAgICAgIH1cblxuICAgICAgICAgIGFuaW1hdGVkRWxzTnVtYmVyLS1cbiAgICAgICAgICBpZiAoYW5pbWF0ZWRFbHNOdW1iZXIgPD0gMCAmJiBlbHNUb1JlbW92ZSkge1xuICAgICAgICAgICAgZWxzVG9SZW1vdmUuZm9yRWFjaChmdW5jdGlvbihlbCkge1xuICAgICAgICAgICAgICAvLyBicm93c2luZyBxdWlja2x5IGNhbiBtYWtlIHRoZSBlbFxuICAgICAgICAgICAgICAvLyBhbHJlYWR5IHJlbW92ZWQgYnkgbGFzdCBwYWdlIHVwZGF0ZSA/XG4gICAgICAgICAgICAgIGlmIChlbC5wYXJlbnROb2RlKSB7XG4gICAgICAgICAgICAgICAgZWwucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChlbClcbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSlcblxuICAgICAgICAgICAgZWxzVG9BZGQuZm9yRWFjaChmdW5jdGlvbihlbCkge1xuICAgICAgICAgICAgICBlbC5jbGFzc05hbWUgPSBlbC5jbGFzc05hbWUucmVwbGFjZShlbC5nZXRBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKSwgXCJcIilcbiAgICAgICAgICAgICAgZWwucmVtb3ZlQXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIilcbiAgICAgICAgICAgICAgLy8gUGpheC5vZmYoZWwsIGFuaW1hdGlvbkV2ZW50TmFtZXMsIHNleHlBbmltYXRpb25FbmQsIHRydWUpXG4gICAgICAgICAgICB9KVxuXG4gICAgICAgICAgICBlbHNUb0FkZCA9IG51bGwgLy8gZnJlZSBtZW1vcnlcbiAgICAgICAgICAgIGVsc1RvUmVtb3ZlID0gbnVsbCAvLyBmcmVlIG1lbW9yeVxuXG4gICAgICAgICAgICAvLyBhc3N1bWUgdGhlIGhlaWdodCBpcyBub3cgdXNlbGVzcyAoYXZvaWQgYnVnIHNpbmNlIHRoZXJlIGlzIG92ZXJmbG93IGhpZGRlbiBvbiB0aGUgcGFyZW50KVxuICAgICAgICAgICAgLy8gb2xkRWwuc3R5bGUuaGVpZ2h0ID0gXCJhdXRvXCJcblxuICAgICAgICAgICAgLy8gdGhpcyBpcyB0byB0cmlnZ2VyIHNvbWUgcmVwYWludCAoZXhhbXBsZTogcGljdHVyZWZpbGwpXG4gICAgICAgICAgICB0aGlzLm9uU3dpdGNoKClcbiAgICAgICAgICAgIC8vIFBqYXgudHJpZ2dlcih3aW5kb3csIFwic2Nyb2xsXCIpXG4gICAgICAgICAgfVxuICAgICAgICB9LmJpbmQodGhpcylcblxuICAgIC8vIEZvcmNlIGhlaWdodCB0byBiZSBhYmxlIHRvIHRyaWdnZXIgY3NzIGFuaW1hdGlvblxuICAgIC8vIGhlcmUgd2UgZ2V0IHRoZSByZWxldmFudCBoZWlnaHRcbiAgICAvLyBvbGRFbC5wYXJlbnROb2RlLmFwcGVuZENoaWxkKG5ld0VsKVxuICAgIC8vIHJlbGV2YW50SGVpZ2h0ID0gbmV3RWwuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0XG4gICAgLy8gb2xkRWwucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChuZXdFbClcbiAgICAvLyBvbGRFbC5zdHlsZS5oZWlnaHQgPSBvbGRFbC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHQgKyBcInB4XCJcblxuICAgIHN3aXRjaE9wdGlvbnMgPSBzd2l0Y2hPcHRpb25zIHx8IHt9XG5cbiAgICBmb3JFYWNoLmNhbGwob2xkRWwuY2hpbGROb2RlcywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIGVsc1RvUmVtb3ZlLnB1c2goZWwpXG4gICAgICBpZiAoZWwuY2xhc3NMaXN0ICYmICFlbC5jbGFzc0xpc3QuY29udGFpbnMoXCJqcy1QamF4LXJlbW92ZVwiKSkge1xuICAgICAgICAvLyBmb3IgZmFzdCBzd2l0Y2gsIGNsZWFuIGVsZW1lbnQgdGhhdCBqdXN0IGhhdmUgYmVlbiBhZGRlZCwgJiBub3QgY2xlYW5lZCB5ZXQuXG4gICAgICAgIGlmIChlbC5oYXNBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKSkge1xuICAgICAgICAgIGVsLmNsYXNzTmFtZSA9IGVsLmNsYXNzTmFtZS5yZXBsYWNlKGVsLmdldEF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpLCBcIlwiKVxuICAgICAgICAgIGVsLnJlbW92ZUF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpXG4gICAgICAgIH1cbiAgICAgICAgZWwuY2xhc3NMaXN0LmFkZChcImpzLVBqYXgtcmVtb3ZlXCIpXG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcyAmJiBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5yZW1vdmVFbGVtZW50KSB7XG4gICAgICAgICAgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MucmVtb3ZlRWxlbWVudChlbClcbiAgICAgICAgfVxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzKSB7XG4gICAgICAgICAgZWwuY2xhc3NOYW1lICs9IFwiIFwiICsgc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLnJlbW92ZSArIFwiIFwiICsgKG9wdGlvbnMuYmFja3dhcmQgPyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuYmFja3dhcmQgOiBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuZm9yd2FyZClcbiAgICAgICAgfVxuICAgICAgICBhbmltYXRlZEVsc051bWJlcisrXG4gICAgICAgIG9uKGVsLCBhbmltYXRpb25FdmVudE5hbWVzLCBzZXh5QW5pbWF0aW9uRW5kLCB0cnVlKVxuICAgICAgfVxuICAgIH0pXG5cbiAgICBmb3JFYWNoLmNhbGwobmV3RWwuY2hpbGROb2RlcywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIGlmIChlbC5jbGFzc0xpc3QpIHtcbiAgICAgICAgdmFyIGFkZENsYXNzZXMgPSBcIlwiXG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMpIHtcbiAgICAgICAgICBhZGRDbGFzc2VzID0gXCIganMtUGpheC1hZGQgXCIgKyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuYWRkICsgXCIgXCIgKyAob3B0aW9ucy5iYWNrd2FyZCA/IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5mb3J3YXJkIDogc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmJhY2t3YXJkKVxuICAgICAgICB9XG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcyAmJiBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5hZGRFbGVtZW50KSB7XG4gICAgICAgICAgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MuYWRkRWxlbWVudChlbClcbiAgICAgICAgfVxuICAgICAgICBlbC5jbGFzc05hbWUgKz0gYWRkQ2xhc3Nlc1xuICAgICAgICBlbC5zZXRBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiLCBhZGRDbGFzc2VzKVxuICAgICAgICBlbHNUb0FkZC5wdXNoKGVsKVxuICAgICAgICBmcmFnVG9BcHBlbmQuYXBwZW5kQ2hpbGQoZWwpXG4gICAgICAgIGFuaW1hdGVkRWxzTnVtYmVyKytcbiAgICAgICAgb24oZWwsIGFuaW1hdGlvbkV2ZW50TmFtZXMsIHNleHlBbmltYXRpb25FbmQsIHRydWUpXG4gICAgICB9XG4gICAgfSlcblxuICAgIC8vIHBhc3MgYWxsIGNsYXNzTmFtZSBvZiB0aGUgcGFyZW50XG4gICAgb2xkRWwuY2xhc3NOYW1lID0gbmV3RWwuY2xhc3NOYW1lXG4gICAgb2xkRWwuYXBwZW5kQ2hpbGQoZnJhZ1RvQXBwZW5kKVxuXG4gICAgLy8gb2xkRWwuc3R5bGUuaGVpZ2h0ID0gcmVsZXZhbnRIZWlnaHQgKyBcInB4XCJcbiAgfVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24oKSB7XG4gIHZhciBjb3VudGVyID0gMFxuICByZXR1cm4gZnVuY3Rpb24oKSB7XG4gICAgdmFyIGlkID0gKFwicGpheFwiICsgKG5ldyBEYXRlKCkuZ2V0VGltZSgpKSkgKyBcIl9cIiArIGNvdW50ZXJcbiAgICBjb3VudGVyKytcbiAgICByZXR1cm4gaWRcbiAgfVxufSkoKVxuIl19
(1)
});
