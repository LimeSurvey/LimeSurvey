(function(f){if(typeof exports==="object"&&typeof module!=="undefined"){module.exports=f()}else if(typeof define==="function"&&define.amd){define([],f)}else{var g;if(typeof window!=="undefined"){g=window}else if(typeof global!=="undefined"){g=global}else if(typeof self!=="undefined"){g=self}else{g=this}g.Pjax = f()}})(function(){var define,module,exports;return (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
var clone = require('./lib/clone.js')
var executeScripts = require('./lib/execute-scripts.js')
var forEachEls = require("./lib/foreach-els.js")
var newUid = require("./lib/uniqueid.js")

var on = require("./lib/events/on.js")
// var off = require("./lib/events/on.js")
var trigger = require("./lib/events/trigger.js")


var Pjax = function(options) {
    this.firstrun = true

    var parseOptions = require("./lib/proto/parse-options.js");
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
  log: require("./lib/proto/log.js"),

  getElements: require("./lib/proto/get-elements.js"),

  parseDOM: require("./lib/proto/parse-dom.js"),

  refresh: require("./lib/proto/refresh.js"),

  reload: require("./lib/reload.js"),

  attachLink: require("./lib/proto/attach-link.js"),

  attachForm: require("./lib/proto/attach-form.js"),

  updateStylesheets: require("./lib/update-stylesheets.js"),

  forEachSelectors: function(cb, context, DOMcontext) {
    return require("./lib/foreach-selectors.js").bind(this)(this.options.selectors, cb, context, DOMcontext)
  },

  switchSelectors: function(selectors, fromEl, toEl, options) {
    return require("./lib/switches-selectors.js").bind(this)(this.options.switches, this.options.switchesOptions, selectors, fromEl, toEl, options)
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
    var tmpEl = document.implementation.createHTMLDocument("pjax")

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

    //reset stylesheets if activated
    if(this.options.reRenderCSS === true){
      this.updateStylesheets.call(this, tmpEl.querySelectorAll('link[rel=stylesheet]'), document.querySelectorAll('link[rel=stylesheet]'));
    }

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
    this.options.selectors.forEach( function(selector) {
      var collectForScriptcomplete = [];

      forEachEls(document.querySelectorAll(selector), function(el) {
        collectForScriptcomplete.push(executeScripts.call(this, el));
      }, this);

      Promise.all(collectForScriptcomplete).then(function(){
        document.dispatchEvent((new Event("pjax:scriptcomplete")));
      });

    },this);
    // }
    // catch(e) {
    //   if (this.options.debug) {
    //     this.log("Pjax switch fail: ", e)
    //   }
    //   this.switchFallback(tmpEl, document)
    // }
  },

  doRequest: require("./lib/request.js"),

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

Pjax.isSupported = require("./lib/is-supported.js");

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

},{"./lib/clone.js":2,"./lib/events/on.js":4,"./lib/events/trigger.js":5,"./lib/execute-scripts.js":6,"./lib/foreach-els.js":7,"./lib/foreach-selectors.js":8,"./lib/is-supported.js":9,"./lib/proto/attach-form.js":11,"./lib/proto/attach-link.js":12,"./lib/proto/get-elements.js":13,"./lib/proto/log.js":14,"./lib/proto/parse-dom.js":15,"./lib/proto/parse-options.js":17,"./lib/proto/refresh.js":18,"./lib/reload.js":19,"./lib/request.js":20,"./lib/switches-selectors.js":21,"./lib/uniqueid.js":23,"./lib/update-stylesheets.js":24}],2:[function(require,module,exports){
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

},{}],3:[function(require,module,exports){
module.exports = function(el) {
  var querySelector = this.options.mainScriptElement;
  var code = (el.text || el.textContent || el.innerHTML || "")
  var src = (el.src || "");
  var parent = el.parentNode || document.querySelector(querySelector) || document.documentElement
  var script = document.createElement("script")
  var promise = null;

  this.log("Evaluating Script: ", el);

  if (code.match("document.write")) {
    if (console && console.log) {
      console.log("Script contains document.write. Can’t be executed correctly. Code skipped ", el)
    }
    return false
  }

  promise = new Promise(function(resolve, reject){

    script.type = "text/javascript"
    if (src != "") {
      script.src = src;
      script.onload = resolve;
      script.async = false; // force asynchronous loading of peripheral js
    }

    if (code != "") {
      try {
        script.appendChild(document.createTextNode(code))
      }
      catch (e) {
        // old IEs have funky script nodes
        script.text = code
      }
      resolve();
    }
  });

  this.log('ParentElement => ', parent );

  // execute
  parent.appendChild(script);
  // avoid pollution only in head or body tags
  if (["head","body"].indexOf( parent.tagName.toLowerCase()) > 0) {
    parent.removeChild(script)
  }

  return promise;
}

},{}],4:[function(require,module,exports){
var forEachEls = require("../foreach-els")

module.exports = function(els, events, listener, useCapture) {
  events = (typeof events === "string" ? events.split(" ") : events)

  events.forEach(function(e) {
    forEachEls(els, function(el) {
      el.addEventListener(e, listener, useCapture)
    })
  })
}

},{"../foreach-els":7}],5:[function(require,module,exports){
var forEachEls = require("../foreach-els")

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

},{"../foreach-els":7}],6:[function(require,module,exports){
var forEachEls = require("./foreach-els")
var evalScript = require("./eval-script")
// Finds and executes scripts (used for newly added elements)
// Needed since innerHTML does not run scripts
module.exports = function(el) {

  this.log("Executing scripts for ", el);

  var loadingScripts = [];

  if(el === undefined) return Promise.resolve();

  if (el.tagName.toLowerCase() === "script") {
    evalScript.call(this, el);
  }

  forEachEls(el.querySelectorAll("script"), function(script) {
    if (!script.type || script.type.toLowerCase() === "text/javascript") {
      // if (script.parentNode) {
      //   script.parentNode.removeChild(script)
      // }
      loadingScripts.push(evalScript.call(this, script));
    }
  }, this);

  return Promise.all(loadingScripts);
}

},{"./eval-script":3,"./foreach-els":7}],7:[function(require,module,exports){
/* global HTMLCollection: true */

module.exports = function(els, fn, context) {
  if (els instanceof HTMLCollection || els instanceof NodeList || els instanceof Array) {
    return Array.prototype.forEach.call(els, fn, context)
  }
  // assume simple dom element
  return fn.call(context, els)
}

},{}],8:[function(require,module,exports){
var forEachEls = require("./foreach-els")

module.exports = function(selectors, cb, context, DOMcontext) {
  DOMcontext = DOMcontext || document
  selectors.forEach(function(selector) {
    forEachEls(DOMcontext.querySelectorAll(selector), cb, context)
  })
}

},{"./foreach-els":7}],9:[function(require,module,exports){
module.exports = function() {
  // Borrowed wholesale from https://github.com/defunkt/jquery-pjax
  return window.history &&
    window.history.pushState &&
    window.history.replaceState &&
    // pushState isn’t reliable on iOS until 5.
    !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]\D|WebApps\/.+CFNetwork)/)
}

},{}],10:[function(require,module,exports){
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

},{}],11:[function(require,module,exports){
require("../polyfills/Function.prototype.bind")

var on = require("../events/on")
var clone = require("../clone")

var attrClick = "data-pjax-click-state"

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

  // Ignore empty anchor "foo.html#"
  if (virtLinkElement.href === window.location.href.split("#")[0] + "#") {
    el.setAttribute(attrClick, "anchor-empty")
    return
  }

  // if declared as a full reload, just normally submit the form
  if ( this.options.currentUrlFullReload) {
    el.setAttribute(attrClick, "reload");
    return;
  }

  event.preventDefault()
  var nameList = [];
  var paramObject = [];
  for(var elementKey in el.elements) {
    var element = el.elements[elementKey];
    if (!!element.name && element.attributes !== undefined && element.tagName.toLowerCase() !== 'button'){
      if (
        (element.type !== 'checkbox' && element.type !== 'radio') || element.checked
      ) {
        if(nameList.indexOf(element.name) === -1){
          nameList.push(element.name);
          paramObject.push({ name: encodeURIComponent(element.name), value: encodeURIComponent(element.value)});
        }
      }
    }
  }



  //Creating a getString
  var paramsString = (paramObject.map(function(value){return value.name+"="+value.value;})).join('&');

  this.options.requestOptions.requestPayload = paramObject;
  this.options.requestOptions.requestPayloadString = paramsString;

  el.setAttribute(attrClick, "submit");

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


    if (event.keyCode == 13) {
      formAction.call(that, el, event)
    }
  }.bind(this))
}

},{"../clone":2,"../events/on":4,"../polyfills/Function.prototype.bind":10}],12:[function(require,module,exports){
require("../polyfills/Function.prototype.bind")

var on = require("../events/on")
var clone = require("../clone")

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

},{"../clone":2,"../events/on":4,"../polyfills/Function.prototype.bind":10}],13:[function(require,module,exports){
module.exports = function(el) {
  return el.querySelectorAll(this.options.elements)
}

},{}],14:[function(require,module,exports){
module.exports = function() {
  if ((this.options.debug && console)) {
    if (typeof console.log === "function") {
      console.log.apply(console, arguments);
    }
    // ie is weird
    else if (console.log) {
      console.log(arguments);
    }
  }
}

},{}],15:[function(require,module,exports){
var forEachEls = require("../foreach-els")

var parseElement = require("./parse-element")

module.exports = function(el) {
  forEachEls(this.getElements(el), parseElement, this)
}

},{"../foreach-els":7,"./parse-element":16}],16:[function(require,module,exports){
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

},{}],17:[function(require,module,exports){
/* global _gaq: true, ga: true */

module.exports = function(options){
  this.options = options
  this.options.elements = this.options.elements || "a[href], form[action]",
  this.options.reRenderCSS = this.options.reRenderCSS || false,
  this.options.mainScriptElement = this.options.mainScriptElement || "head"
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

},{}],18:[function(require,module,exports){
module.exports = function(el) {
  this.parseDOM(el || document)
}

},{}],19:[function(require,module,exports){
module.exports = function() {
  window.location.reload()
}

},{}],20:[function(require,module,exports){
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

},{}],21:[function(require,module,exports){
var forEachEls = require("./foreach-els")

var defaultSwitches = require("./switches")

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

},{"./foreach-els":7,"./switches":22}],22:[function(require,module,exports){
var on = require("./events/on.js")
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

},{"./events/on.js":4}],23:[function(require,module,exports){
module.exports = (function() {
  var counter = 0
  return function() {
    var id = ("pjax" + (new Date().getTime())) + "_" + counter
    counter++
    return id
  }
})()

},{}],24:[function(require,module,exports){
var forEachEls = require("./foreach-els")

module.exports = function(elements, oldElements) {
   this.log("styleheets old elements", oldElements);
   this.log("styleheets new elements", elements);
  var toArray = function(enumerable){
      var arr = [];
      for(var i = enumerable.length; i--; arr.unshift(enumerable[i]));
      return arr;
  };
  forEachEls(elements, function(newEl, i) {
    var oldElementsArray = toArray(oldElements);
    var resemblingOld = oldElementsArray.reduce(function(acc, oldEl){ 
      acc = ((oldEl.href === newEl.href) ? oldEl : acc);  
      return acc;
    }, null);

    if(resemblingOld !== null){
      if (this.log) {
        this.log("old stylesheet found not resetting");
      }
    } else {
      if (this.log) {
        this.log("new stylesheet => add to head");
      }
      var head = document.getElementsByTagName( 'head' )[0], 
       link = document.createElement( 'link' );           
        link.setAttribute( 'href', newEl.href );
        link.setAttribute( 'rel', 'stylesheet' );
        link.setAttribute( 'type', 'text/css' );
    }
  }, this);

}

},{"./foreach-els":7}]},{},[1])(1)
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi4uLy4uL25vZGUvbGliL25vZGVfbW9kdWxlcy9icm93c2VyaWZ5L25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJpbmRleC5qcyIsImxpYi9jbG9uZS5qcyIsImxpYi9ldmFsLXNjcmlwdC5qcyIsImxpYi9ldmVudHMvb24uanMiLCJsaWIvZXZlbnRzL3RyaWdnZXIuanMiLCJsaWIvZXhlY3V0ZS1zY3JpcHRzLmpzIiwibGliL2ZvcmVhY2gtZWxzLmpzIiwibGliL2ZvcmVhY2gtc2VsZWN0b3JzLmpzIiwibGliL2lzLXN1cHBvcnRlZC5qcyIsImxpYi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQuanMiLCJsaWIvcHJvdG8vYXR0YWNoLWZvcm0uanMiLCJsaWIvcHJvdG8vYXR0YWNoLWxpbmsuanMiLCJsaWIvcHJvdG8vZ2V0LWVsZW1lbnRzLmpzIiwibGliL3Byb3RvL2xvZy5qcyIsImxpYi9wcm90by9wYXJzZS1kb20uanMiLCJsaWIvcHJvdG8vcGFyc2UtZWxlbWVudC5qcyIsImxpYi9wcm90by9wYXJzZS1vcHRpb25zLmpzIiwibGliL3Byb3RvL3JlZnJlc2guanMiLCJsaWIvcmVsb2FkLmpzIiwibGliL3JlcXVlc3QuanMiLCJsaWIvc3dpdGNoZXMtc2VsZWN0b3JzLmpzIiwibGliL3N3aXRjaGVzLmpzIiwibGliL3VuaXF1ZWlkLmpzIiwibGliL3VwZGF0ZS1zdHlsZXNoZWV0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNqUUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNqREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDL0JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzNCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNUQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pGQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNQQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDeENBO0FBQ0E7QUFDQTtBQUNBOztBQ0hBO0FBQ0E7QUFDQTtBQUNBOztBQ0hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25DQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25IQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQSIsImZpbGUiOiJnZW5lcmF0ZWQuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlc0NvbnRlbnQiOlsiKGZ1bmN0aW9uIGUodCxuLHIpe2Z1bmN0aW9uIHMobyx1KXtpZighbltvXSl7aWYoIXRbb10pe3ZhciBhPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7aWYoIXUmJmEpcmV0dXJuIGEobywhMCk7aWYoaSlyZXR1cm4gaShvLCEwKTt2YXIgZj1uZXcgRXJyb3IoXCJDYW5ub3QgZmluZCBtb2R1bGUgJ1wiK28rXCInXCIpO3Rocm93IGYuY29kZT1cIk1PRFVMRV9OT1RfRk9VTkRcIixmfXZhciBsPW5bb109e2V4cG9ydHM6e319O3Rbb11bMF0uY2FsbChsLmV4cG9ydHMsZnVuY3Rpb24oZSl7dmFyIG49dFtvXVsxXVtlXTtyZXR1cm4gcyhuP246ZSl9LGwsbC5leHBvcnRzLGUsdCxuLHIpfXJldHVybiBuW29dLmV4cG9ydHN9dmFyIGk9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtmb3IodmFyIG89MDtvPHIubGVuZ3RoO28rKylzKHJbb10pO3JldHVybiBzfSkiLCJ2YXIgY2xvbmUgPSByZXF1aXJlKCcuL2xpYi9jbG9uZS5qcycpXG52YXIgZXhlY3V0ZVNjcmlwdHMgPSByZXF1aXJlKCcuL2xpYi9leGVjdXRlLXNjcmlwdHMuanMnKVxudmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9saWIvZm9yZWFjaC1lbHMuanNcIilcbnZhciBuZXdVaWQgPSByZXF1aXJlKFwiLi9saWIvdW5pcXVlaWQuanNcIilcblxudmFyIG9uID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy9vbi5qc1wiKVxuLy8gdmFyIG9mZiA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvb24uanNcIilcbnZhciB0cmlnZ2VyID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy90cmlnZ2VyLmpzXCIpXG5cblxudmFyIFBqYXggPSBmdW5jdGlvbihvcHRpb25zKSB7XG4gICAgdGhpcy5maXJzdHJ1biA9IHRydWVcblxuICAgIHZhciBwYXJzZU9wdGlvbnMgPSByZXF1aXJlKFwiLi9saWIvcHJvdG8vcGFyc2Utb3B0aW9ucy5qc1wiKTtcbiAgICBwYXJzZU9wdGlvbnMuYXBwbHkodGhpcyxbb3B0aW9uc10pXG4gICAgdGhpcy5sb2coXCJQamF4IG9wdGlvbnNcIiwgdGhpcy5vcHRpb25zKVxuXG4gICAgdGhpcy5tYXhVaWQgPSB0aGlzLmxhc3RVaWQgPSBuZXdVaWQoKVxuXG4gICAgdGhpcy5wYXJzZURPTShkb2N1bWVudClcblxuICAgIG9uKHdpbmRvdywgXCJwb3BzdGF0ZVwiLCBmdW5jdGlvbihzdCkge1xuICAgICAgaWYgKHN0LnN0YXRlKSB7XG4gICAgICAgIHZhciBvcHQgPSBjbG9uZSh0aGlzLm9wdGlvbnMpXG4gICAgICAgIG9wdC51cmwgPSBzdC5zdGF0ZS51cmxcbiAgICAgICAgb3B0LnRpdGxlID0gc3Quc3RhdGUudGl0bGVcbiAgICAgICAgb3B0Lmhpc3RvcnkgPSBmYWxzZVxuICAgICAgICBvcHQucmVxdWVzdE9wdGlvbnMgPSB7fTtcbiAgICAgICAgaWYgKHN0LnN0YXRlLnVpZCA8IHRoaXMubGFzdFVpZCkge1xuICAgICAgICAgIG9wdC5iYWNrd2FyZCA9IHRydWVcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICBvcHQuZm9yd2FyZCA9IHRydWVcbiAgICAgICAgfVxuICAgICAgICB0aGlzLmxhc3RVaWQgPSBzdC5zdGF0ZS51aWRcblxuICAgICAgICAvLyBAdG9kbyBpbXBsZW1lbnQgaGlzdG9yeSBjYWNoZSBoZXJlLCBiYXNlZCBvbiB1aWRcbiAgICAgICAgdGhpcy5sb2FkVXJsKHN0LnN0YXRlLnVybCwgb3B0KVxuICAgICAgfVxuICAgIH0uYmluZCh0aGlzKSlcbiAgfVxuXG5QamF4LnByb3RvdHlwZSA9IHtcbiAgbG9nOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vbG9nLmpzXCIpLFxuXG4gIGdldEVsZW1lbnRzOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vZ2V0LWVsZW1lbnRzLmpzXCIpLFxuXG4gIHBhcnNlRE9NOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vcGFyc2UtZG9tLmpzXCIpLFxuXG4gIHJlZnJlc2g6IHJlcXVpcmUoXCIuL2xpYi9wcm90by9yZWZyZXNoLmpzXCIpLFxuXG4gIHJlbG9hZDogcmVxdWlyZShcIi4vbGliL3JlbG9hZC5qc1wiKSxcblxuICBhdHRhY2hMaW5rOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vYXR0YWNoLWxpbmsuanNcIiksXG5cbiAgYXR0YWNoRm9ybTogcmVxdWlyZShcIi4vbGliL3Byb3RvL2F0dGFjaC1mb3JtLmpzXCIpLFxuXG4gIHVwZGF0ZVN0eWxlc2hlZXRzOiByZXF1aXJlKFwiLi9saWIvdXBkYXRlLXN0eWxlc2hlZXRzLmpzXCIpLFxuXG4gIGZvckVhY2hTZWxlY3RvcnM6IGZ1bmN0aW9uKGNiLCBjb250ZXh0LCBET01jb250ZXh0KSB7XG4gICAgcmV0dXJuIHJlcXVpcmUoXCIuL2xpYi9mb3JlYWNoLXNlbGVjdG9ycy5qc1wiKS5iaW5kKHRoaXMpKHRoaXMub3B0aW9ucy5zZWxlY3RvcnMsIGNiLCBjb250ZXh0LCBET01jb250ZXh0KVxuICB9LFxuXG4gIHN3aXRjaFNlbGVjdG9yczogZnVuY3Rpb24oc2VsZWN0b3JzLCBmcm9tRWwsIHRvRWwsIG9wdGlvbnMpIHtcbiAgICByZXR1cm4gcmVxdWlyZShcIi4vbGliL3N3aXRjaGVzLXNlbGVjdG9ycy5qc1wiKS5iaW5kKHRoaXMpKHRoaXMub3B0aW9ucy5zd2l0Y2hlcywgdGhpcy5vcHRpb25zLnN3aXRjaGVzT3B0aW9ucywgc2VsZWN0b3JzLCBmcm9tRWwsIHRvRWwsIG9wdGlvbnMpXG4gIH0sXG5cblxuICAvLyB0b28gbXVjaCBwcm9ibGVtIHdpdGggdGhlIGNvZGUgYmVsb3dcbiAgLy8gKyBpdOKAmXMgdG9vIGRhbmdlcm91c1xuLy8gICBzd2l0Y2hGYWxsYmFjazogZnVuY3Rpb24oZnJvbUVsLCB0b0VsKSB7XG4vLyAgICAgdGhpcy5zd2l0Y2hTZWxlY3RvcnMoW1wiaGVhZFwiLCBcImJvZHlcIl0sIGZyb21FbCwgdG9FbClcbi8vICAgICAvLyBleGVjdXRlIHNjcmlwdCB3aGVuIERPTSBpcyBsaWtlIGl0IHNob3VsZCBiZVxuLy8gICAgIFBqYXguZXhlY3V0ZVNjcmlwdHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcImhlYWRcIikpXG4vLyAgICAgUGpheC5leGVjdXRlU2NyaXB0cyhkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiYm9keVwiKSlcbi8vICAgfVxuXG4gIGxhdGVzdENoYW5jZTogZnVuY3Rpb24oaHJlZikge1xuICAgIHdpbmRvdy5sb2NhdGlvbiA9IGhyZWZcbiAgfSxcblxuICBvblN3aXRjaDogZnVuY3Rpb24oKSB7XG4gICAgdHJpZ2dlcih3aW5kb3csIFwicmVzaXplIHNjcm9sbFwiKVxuICB9LFxuXG4gIGxvYWRDb250ZW50OiBmdW5jdGlvbihodG1sLCBvcHRpb25zKSB7XG4gICAgdmFyIHRtcEVsID0gZG9jdW1lbnQuaW1wbGVtZW50YXRpb24uY3JlYXRlSFRNTERvY3VtZW50KFwicGpheFwiKVxuXG4gICAgLy8gcGFyc2UgSFRNTCBhdHRyaWJ1dGVzIHRvIGNvcHkgdGhlbVxuICAgIC8vIHNpbmNlIHdlIGFyZSBmb3JjZWQgdG8gdXNlIGRvY3VtZW50RWxlbWVudC5pbm5lckhUTUwgKG91dGVySFRNTCBjYW4ndCBiZSB1c2VkIGZvciA8aHRtbD4pXG4gICAgdmFyIGh0bWxSZWdleCA9IC88aHRtbFtePl0rPi9naVxuICAgIHZhciBodG1sQXR0cmlic1JlZ2V4ID0gL1xccz9bYS16Ol0rKD86XFw9KD86XFwnfFxcXCIpW15cXCdcXFwiPl0rKD86XFwnfFxcXCIpKSovZ2lcbiAgICB2YXIgbWF0Y2hlcyA9IGh0bWwubWF0Y2goaHRtbFJlZ2V4KVxuICAgIGlmIChtYXRjaGVzICYmIG1hdGNoZXMubGVuZ3RoKSB7XG4gICAgICBtYXRjaGVzID0gbWF0Y2hlc1swXS5tYXRjaChodG1sQXR0cmlic1JlZ2V4KVxuICAgICAgaWYgKG1hdGNoZXMubGVuZ3RoKSB7XG4gICAgICAgIG1hdGNoZXMuc2hpZnQoKVxuICAgICAgICBtYXRjaGVzLmZvckVhY2goZnVuY3Rpb24oaHRtbEF0dHJpYikge1xuICAgICAgICAgIHZhciBhdHRyID0gaHRtbEF0dHJpYi50cmltKCkuc3BsaXQoXCI9XCIpXG4gICAgICAgICAgaWYgKGF0dHIubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuc2V0QXR0cmlidXRlKGF0dHJbMF0sIHRydWUpXG4gICAgICAgICAgfVxuICAgICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgdG1wRWwuZG9jdW1lbnRFbGVtZW50LnNldEF0dHJpYnV0ZShhdHRyWzBdLCBhdHRyWzFdLnNsaWNlKDEsIC0xKSlcbiAgICAgICAgICB9XG4gICAgICAgIH0pXG4gICAgICB9XG4gICAgfVxuXG4gICAgdG1wRWwuZG9jdW1lbnRFbGVtZW50LmlubmVySFRNTCA9IGh0bWxcbiAgICB0aGlzLmxvZyhcImxvYWQgY29udGVudFwiLCB0bXBFbC5kb2N1bWVudEVsZW1lbnQuYXR0cmlidXRlcywgdG1wRWwuZG9jdW1lbnRFbGVtZW50LmlubmVySFRNTC5sZW5ndGgpXG5cbiAgICAvLyBDbGVhciBvdXQgYW55IGZvY3VzZWQgY29udHJvbHMgYmVmb3JlIGluc2VydGluZyBuZXcgcGFnZSBjb250ZW50cy5cbiAgICAvLyB3ZSBjbGVhciBmb2N1cyBvbiBub24gZm9ybSBlbGVtZW50c1xuICAgIGlmIChkb2N1bWVudC5hY3RpdmVFbGVtZW50ICYmICFkb2N1bWVudC5hY3RpdmVFbGVtZW50LnZhbHVlKSB7XG4gICAgICB0cnkge1xuICAgICAgICBkb2N1bWVudC5hY3RpdmVFbGVtZW50LmJsdXIoKVxuICAgICAgfSBjYXRjaCAoZSkgeyB9XG4gICAgfVxuXG4gICAgLy8gdHJ5IHtcbiAgICB0aGlzLnN3aXRjaFNlbGVjdG9ycyh0aGlzLm9wdGlvbnMuc2VsZWN0b3JzLCB0bXBFbCwgZG9jdW1lbnQsIG9wdGlvbnMpXG5cbiAgICAvL3Jlc2V0IHN0eWxlc2hlZXRzIGlmIGFjdGl2YXRlZFxuICAgIGlmKHRoaXMub3B0aW9ucy5yZVJlbmRlckNTUyA9PT0gdHJ1ZSl7XG4gICAgICB0aGlzLnVwZGF0ZVN0eWxlc2hlZXRzLmNhbGwodGhpcywgdG1wRWwucXVlcnlTZWxlY3RvckFsbCgnbGlua1tyZWw9c3R5bGVzaGVldF0nKSwgZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnbGlua1tyZWw9c3R5bGVzaGVldF0nKSk7XG4gICAgfVxuXG4gICAgLy8gRkYgYnVnOiBXb27igJl0IGF1dG9mb2N1cyBmaWVsZHMgdGhhdCBhcmUgaW5zZXJ0ZWQgdmlhIEpTLlxuICAgIC8vIFRoaXMgYmVoYXZpb3IgaXMgaW5jb3JyZWN0LiBTbyBpZiB0aGVyZXMgbm8gY3VycmVudCBmb2N1cywgYXV0b2ZvY3VzXG4gICAgLy8gdGhlIGxhc3QgZmllbGQuXG4gICAgLy9cbiAgICAvLyBodHRwOi8vd3d3LnczLm9yZy9odG1sL3dnL2RyYWZ0cy9odG1sL21hc3Rlci9mb3Jtcy5odG1sXG4gICAgdmFyIGF1dG9mb2N1c0VsID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcIlthdXRvZm9jdXNdXCIpKS5wb3AoKVxuICAgIGlmIChhdXRvZm9jdXNFbCAmJiBkb2N1bWVudC5hY3RpdmVFbGVtZW50ICE9PSBhdXRvZm9jdXNFbCkge1xuICAgICAgYXV0b2ZvY3VzRWwuZm9jdXMoKTtcbiAgICB9XG5cbiAgICAvLyBleGVjdXRlIHNjcmlwdHMgd2hlbiBET00gaGF2ZSBiZWVuIGNvbXBsZXRlbHkgdXBkYXRlZFxuICAgIHRoaXMub3B0aW9ucy5zZWxlY3RvcnMuZm9yRWFjaCggZnVuY3Rpb24oc2VsZWN0b3IpIHtcbiAgICAgIHZhciBjb2xsZWN0Rm9yU2NyaXB0Y29tcGxldGUgPSBbXTtcblxuICAgICAgZm9yRWFjaEVscyhkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKSwgZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgY29sbGVjdEZvclNjcmlwdGNvbXBsZXRlLnB1c2goZXhlY3V0ZVNjcmlwdHMuY2FsbCh0aGlzLCBlbCkpO1xuICAgICAgfSwgdGhpcyk7XG5cbiAgICAgIFByb21pc2UuYWxsKGNvbGxlY3RGb3JTY3JpcHRjb21wbGV0ZSkudGhlbihmdW5jdGlvbigpe1xuICAgICAgICBkb2N1bWVudC5kaXNwYXRjaEV2ZW50KChuZXcgRXZlbnQoXCJwamF4OnNjcmlwdGNvbXBsZXRlXCIpKSk7XG4gICAgICB9KTtcblxuICAgIH0sdGhpcyk7XG4gICAgLy8gfVxuICAgIC8vIGNhdGNoKGUpIHtcbiAgICAvLyAgIGlmICh0aGlzLm9wdGlvbnMuZGVidWcpIHtcbiAgICAvLyAgICAgdGhpcy5sb2coXCJQamF4IHN3aXRjaCBmYWlsOiBcIiwgZSlcbiAgICAvLyAgIH1cbiAgICAvLyAgIHRoaXMuc3dpdGNoRmFsbGJhY2sodG1wRWwsIGRvY3VtZW50KVxuICAgIC8vIH1cbiAgfSxcblxuICBkb1JlcXVlc3Q6IHJlcXVpcmUoXCIuL2xpYi9yZXF1ZXN0LmpzXCIpLFxuXG4gIGxvYWRVcmw6IGZ1bmN0aW9uKGhyZWYsIG9wdGlvbnMpIHtcbiAgICB0aGlzLmxvZyhcImxvYWQgaHJlZlwiLCBocmVmLCBvcHRpb25zKVxuXG4gICAgdHJpZ2dlcihkb2N1bWVudCwgXCJwamF4OnNlbmRcIiwgb3B0aW9ucyk7XG5cbiAgICAvLyBEbyB0aGUgcmVxdWVzdFxuICAgIHRoaXMuZG9SZXF1ZXN0KGhyZWYsIG9wdGlvbnMucmVxdWVzdE9wdGlvbnMsIGZ1bmN0aW9uKGh0bWwpIHtcbiAgICAgIC8vIEZhaWwgaWYgdW5hYmxlIHRvIGxvYWQgSFRNTCB2aWEgQUpBWFxuICAgICAgaWYgKGh0bWwgPT09IGZhbHNlKSB7XG4gICAgICAgIHRyaWdnZXIoZG9jdW1lbnQsXCJwamF4OmNvbXBsZXRlIHBqYXg6ZXJyb3JcIiwgb3B0aW9ucylcblxuICAgICAgICByZXR1cm5cbiAgICAgIH1cblxuICAgICAgLy8gQ2xlYXIgb3V0IGFueSBmb2N1c2VkIGNvbnRyb2xzIGJlZm9yZSBpbnNlcnRpbmcgbmV3IHBhZ2UgY29udGVudHMuXG4gICAgICBkb2N1bWVudC5hY3RpdmVFbGVtZW50LmJsdXIoKVxuXG4gICAgICB0cnkge1xuICAgICAgICB0aGlzLmxvYWRDb250ZW50KGh0bWwsIG9wdGlvbnMpXG4gICAgICB9XG4gICAgICBjYXRjaCAoZSkge1xuICAgICAgICBpZiAoIXRoaXMub3B0aW9ucy5kZWJ1Zykge1xuICAgICAgICAgIGlmIChjb25zb2xlICYmIGNvbnNvbGUuZXJyb3IpIHtcbiAgICAgICAgICAgIGNvbnNvbGUuZXJyb3IoXCJQamF4IHN3aXRjaCBmYWlsOiBcIiwgZSlcbiAgICAgICAgICB9XG4gICAgICAgICAgdGhpcy5sYXRlc3RDaGFuY2UoaHJlZilcbiAgICAgICAgICByZXR1cm5cbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICB0aHJvdyBlXG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgICAgaWYgKG9wdGlvbnMuaGlzdG9yeSkge1xuICAgICAgICBpZiAodGhpcy5maXJzdHJ1bikge1xuICAgICAgICAgIHRoaXMubGFzdFVpZCA9IHRoaXMubWF4VWlkID0gbmV3VWlkKClcbiAgICAgICAgICB0aGlzLmZpcnN0cnVuID0gZmFsc2VcbiAgICAgICAgICB3aW5kb3cuaGlzdG9yeS5yZXBsYWNlU3RhdGUoe1xuICAgICAgICAgICAgdXJsOiB3aW5kb3cubG9jYXRpb24uaHJlZixcbiAgICAgICAgICAgIHRpdGxlOiBkb2N1bWVudC50aXRsZSxcbiAgICAgICAgICAgIHVpZDogdGhpcy5tYXhVaWRcbiAgICAgICAgICB9LFxuICAgICAgICAgIGRvY3VtZW50LnRpdGxlKVxuICAgICAgICB9XG5cbiAgICAgICAgLy8gVXBkYXRlIGJyb3dzZXIgaGlzdG9yeVxuICAgICAgICB0aGlzLmxhc3RVaWQgPSB0aGlzLm1heFVpZCA9IG5ld1VpZCgpXG4gICAgICAgIHdpbmRvdy5oaXN0b3J5LnB1c2hTdGF0ZSh7XG4gICAgICAgICAgdXJsOiBocmVmLFxuICAgICAgICAgIHRpdGxlOiBvcHRpb25zLnRpdGxlLFxuICAgICAgICAgIHVpZDogdGhpcy5tYXhVaWRcbiAgICAgICAgfSxcbiAgICAgICAgICBvcHRpb25zLnRpdGxlLFxuICAgICAgICAgIGhyZWYpXG4gICAgICB9XG5cbiAgICAgIHRoaXMuZm9yRWFjaFNlbGVjdG9ycyhmdW5jdGlvbihlbCkge1xuICAgICAgICB0aGlzLnBhcnNlRE9NKGVsKVxuICAgICAgfSwgdGhpcylcblxuICAgICAgLy8gRmlyZSBFdmVudHNcbiAgICAgIHRyaWdnZXIoZG9jdW1lbnQsXCJwamF4OmNvbXBsZXRlIHBqYXg6c3VjY2Vzc1wiLCBvcHRpb25zKVxuXG4gICAgICBvcHRpb25zLmFuYWx5dGljcygpXG5cbiAgICAgIC8vIFNjcm9sbCBwYWdlIHRvIHRvcCBvbiBuZXcgcGFnZSBsb2FkXG4gICAgICBpZiAob3B0aW9ucy5zY3JvbGxUbyAhPT0gZmFsc2UpIHtcbiAgICAgICAgaWYgKG9wdGlvbnMuc2Nyb2xsVG8ubGVuZ3RoID4gMSkge1xuICAgICAgICAgIHdpbmRvdy5zY3JvbGxUbyhvcHRpb25zLnNjcm9sbFRvWzBdLCBvcHRpb25zLnNjcm9sbFRvWzFdKVxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgIHdpbmRvdy5zY3JvbGxUbygwLCBvcHRpb25zLnNjcm9sbFRvKVxuICAgICAgICB9XG4gICAgICB9XG4gICAgfS5iaW5kKHRoaXMpKVxuICB9XG59XG5cblBqYXguaXNTdXBwb3J0ZWQgPSByZXF1aXJlKFwiLi9saWIvaXMtc3VwcG9ydGVkLmpzXCIpO1xuXG4vL2FyZ3VhYmx5IGNvdWxkIGRvIGBpZiggcmVxdWlyZShcIi4vbGliL2lzLXN1cHBvcnRlZC5qc1wiKSgpKSB7YCBidXQgdGhhdCBtaWdodCBiZSBhIGxpdHRsZSB0byBzaW1wbGVcbmlmIChQamF4LmlzU3VwcG9ydGVkKCkpIHtcbiAgbW9kdWxlLmV4cG9ydHMgPSBQamF4XG59XG4vLyBpZiB0aGVyZSBpc27igJl0IHJlcXVpcmVkIGJyb3dzZXIgZnVuY3Rpb25zLCByZXR1cm5pbmcgc3R1cGlkIGFwaVxuZWxzZSB7XG4gIHZhciBzdHVwaWRQamF4ID0gZnVuY3Rpb24oKSB7fVxuICBmb3IgKHZhciBrZXkgaW4gUGpheC5wcm90b3R5cGUpIHtcbiAgICBpZiAoUGpheC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkoa2V5KSAmJiB0eXBlb2YgUGpheC5wcm90b3R5cGVba2V5XSA9PT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICBzdHVwaWRQamF4W2tleV0gPSBzdHVwaWRQamF4XG4gICAgfVxuICB9XG5cbiAgbW9kdWxlLmV4cG9ydHMgPSBzdHVwaWRQamF4XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKG9iaikge1xuICBpZiAobnVsbCA9PT0gb2JqIHx8IFwib2JqZWN0XCIgIT0gdHlwZW9mIG9iaikge1xuICAgIHJldHVybiBvYmpcbiAgfVxuICB2YXIgY29weSA9IG9iai5jb25zdHJ1Y3RvcigpXG4gIGZvciAodmFyIGF0dHIgaW4gb2JqKSB7XG4gICAgaWYgKG9iai5oYXNPd25Qcm9wZXJ0eShhdHRyKSkge1xuICAgICAgY29weVthdHRyXSA9IG9ialthdHRyXVxuICAgIH1cbiAgfVxuICByZXR1cm4gY29weVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB2YXIgcXVlcnlTZWxlY3RvciA9IHRoaXMub3B0aW9ucy5tYWluU2NyaXB0RWxlbWVudDtcbiAgdmFyIGNvZGUgPSAoZWwudGV4dCB8fCBlbC50ZXh0Q29udGVudCB8fCBlbC5pbm5lckhUTUwgfHwgXCJcIilcbiAgdmFyIHNyYyA9IChlbC5zcmMgfHwgXCJcIik7XG4gIHZhciBwYXJlbnQgPSBlbC5wYXJlbnROb2RlIHx8IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IocXVlcnlTZWxlY3RvcikgfHwgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50XG4gIHZhciBzY3JpcHQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KFwic2NyaXB0XCIpXG4gIHZhciBwcm9taXNlID0gbnVsbDtcblxuICB0aGlzLmxvZyhcIkV2YWx1YXRpbmcgU2NyaXB0OiBcIiwgZWwpO1xuXG4gIGlmIChjb2RlLm1hdGNoKFwiZG9jdW1lbnQud3JpdGVcIikpIHtcbiAgICBpZiAoY29uc29sZSAmJiBjb25zb2xlLmxvZykge1xuICAgICAgY29uc29sZS5sb2coXCJTY3JpcHQgY29udGFpbnMgZG9jdW1lbnQud3JpdGUuIENhbuKAmXQgYmUgZXhlY3V0ZWQgY29ycmVjdGx5LiBDb2RlIHNraXBwZWQgXCIsIGVsKVxuICAgIH1cbiAgICByZXR1cm4gZmFsc2VcbiAgfVxuXG4gIHByb21pc2UgPSBuZXcgUHJvbWlzZShmdW5jdGlvbihyZXNvbHZlLCByZWplY3Qpe1xuXG4gICAgc2NyaXB0LnR5cGUgPSBcInRleHQvamF2YXNjcmlwdFwiXG4gICAgaWYgKHNyYyAhPSBcIlwiKSB7XG4gICAgICBzY3JpcHQuc3JjID0gc3JjO1xuICAgICAgc2NyaXB0Lm9ubG9hZCA9IHJlc29sdmU7XG4gICAgICBzY3JpcHQuYXN5bmMgPSBmYWxzZTsgLy8gZm9yY2UgYXN5bmNocm9ub3VzIGxvYWRpbmcgb2YgcGVyaXBoZXJhbCBqc1xuICAgIH1cblxuICAgIGlmIChjb2RlICE9IFwiXCIpIHtcbiAgICAgIHRyeSB7XG4gICAgICAgIHNjcmlwdC5hcHBlbmRDaGlsZChkb2N1bWVudC5jcmVhdGVUZXh0Tm9kZShjb2RlKSlcbiAgICAgIH1cbiAgICAgIGNhdGNoIChlKSB7XG4gICAgICAgIC8vIG9sZCBJRXMgaGF2ZSBmdW5reSBzY3JpcHQgbm9kZXNcbiAgICAgICAgc2NyaXB0LnRleHQgPSBjb2RlXG4gICAgICB9XG4gICAgICByZXNvbHZlKCk7XG4gICAgfVxuICB9KTtcblxuICB0aGlzLmxvZygnUGFyZW50RWxlbWVudCA9PiAnLCBwYXJlbnQgKTtcblxuICAvLyBleGVjdXRlXG4gIHBhcmVudC5hcHBlbmRDaGlsZChzY3JpcHQpO1xuICAvLyBhdm9pZCBwb2xsdXRpb24gb25seSBpbiBoZWFkIG9yIGJvZHkgdGFnc1xuICBpZiAoW1wiaGVhZFwiLFwiYm9keVwiXS5pbmRleE9mKCBwYXJlbnQudGFnTmFtZS50b0xvd2VyQ2FzZSgpKSA+IDApIHtcbiAgICBwYXJlbnQucmVtb3ZlQ2hpbGQoc2NyaXB0KVxuICB9XG5cbiAgcmV0dXJuIHByb21pc2U7XG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVscywgZXZlbnRzLCBsaXN0ZW5lciwgdXNlQ2FwdHVyZSkge1xuICBldmVudHMgPSAodHlwZW9mIGV2ZW50cyA9PT0gXCJzdHJpbmdcIiA/IGV2ZW50cy5zcGxpdChcIiBcIikgOiBldmVudHMpXG5cbiAgZXZlbnRzLmZvckVhY2goZnVuY3Rpb24oZSkge1xuICAgIGZvckVhY2hFbHMoZWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgZWwuYWRkRXZlbnRMaXN0ZW5lcihlLCBsaXN0ZW5lciwgdXNlQ2FwdHVyZSlcbiAgICB9KVxuICB9KVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbHMsIGV2ZW50cywgb3B0cykge1xuICBldmVudHMgPSAodHlwZW9mIGV2ZW50cyA9PT0gXCJzdHJpbmdcIiA/IGV2ZW50cy5zcGxpdChcIiBcIikgOiBldmVudHMpXG5cbiAgZXZlbnRzLmZvckVhY2goZnVuY3Rpb24oZSkge1xuICAgIHZhciBldmVudCAvLyA9IG5ldyBDdXN0b21FdmVudChlKSAvLyBkb2Vzbid0IGV2ZXJ5d2hlcmUgeWV0XG4gICAgZXZlbnQgPSBkb2N1bWVudC5jcmVhdGVFdmVudChcIkhUTUxFdmVudHNcIilcbiAgICBldmVudC5pbml0RXZlbnQoZSwgdHJ1ZSwgdHJ1ZSlcbiAgICBldmVudC5ldmVudE5hbWUgPSBlXG4gICAgaWYgKG9wdHMpIHtcbiAgICAgIE9iamVjdC5rZXlzKG9wdHMpLmZvckVhY2goZnVuY3Rpb24oa2V5KSB7XG4gICAgICAgIGV2ZW50W2tleV0gPSBvcHRzW2tleV1cbiAgICAgIH0pXG4gICAgfVxuXG4gICAgZm9yRWFjaEVscyhlbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICB2YXIgZG9tRml4ID0gZmFsc2VcbiAgICAgIGlmICghZWwucGFyZW50Tm9kZSAmJiBlbCAhPT0gZG9jdW1lbnQgJiYgZWwgIT09IHdpbmRvdykge1xuICAgICAgICAvLyBUSEFOS1MgWU9VIElFICg5LzEwLy8xMSBjb25jZXJuZWQpXG4gICAgICAgIC8vIGRpc3BhdGNoRXZlbnQgZG9lc24ndCB3b3JrIGlmIGVsZW1lbnQgaXMgbm90IGluIHRoZSBkb21cbiAgICAgICAgZG9tRml4ID0gdHJ1ZVxuICAgICAgICBkb2N1bWVudC5ib2R5LmFwcGVuZENoaWxkKGVsKVxuICAgICAgfVxuICAgICAgZWwuZGlzcGF0Y2hFdmVudChldmVudClcbiAgICAgIGlmIChkb21GaXgpIHtcbiAgICAgICAgZWwucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChlbClcbiAgICAgIH1cbiAgICB9KVxuICB9KVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9mb3JlYWNoLWVsc1wiKVxudmFyIGV2YWxTY3JpcHQgPSByZXF1aXJlKFwiLi9ldmFsLXNjcmlwdFwiKVxuLy8gRmluZHMgYW5kIGV4ZWN1dGVzIHNjcmlwdHMgKHVzZWQgZm9yIG5ld2x5IGFkZGVkIGVsZW1lbnRzKVxuLy8gTmVlZGVkIHNpbmNlIGlubmVySFRNTCBkb2VzIG5vdCBydW4gc2NyaXB0c1xubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuXG4gIHRoaXMubG9nKFwiRXhlY3V0aW5nIHNjcmlwdHMgZm9yIFwiLCBlbCk7XG5cbiAgdmFyIGxvYWRpbmdTY3JpcHRzID0gW107XG5cbiAgaWYoZWwgPT09IHVuZGVmaW5lZCkgcmV0dXJuIFByb21pc2UucmVzb2x2ZSgpO1xuXG4gIGlmIChlbC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgPT09IFwic2NyaXB0XCIpIHtcbiAgICBldmFsU2NyaXB0LmNhbGwodGhpcywgZWwpO1xuICB9XG5cbiAgZm9yRWFjaEVscyhlbC5xdWVyeVNlbGVjdG9yQWxsKFwic2NyaXB0XCIpLCBmdW5jdGlvbihzY3JpcHQpIHtcbiAgICBpZiAoIXNjcmlwdC50eXBlIHx8IHNjcmlwdC50eXBlLnRvTG93ZXJDYXNlKCkgPT09IFwidGV4dC9qYXZhc2NyaXB0XCIpIHtcbiAgICAgIC8vIGlmIChzY3JpcHQucGFyZW50Tm9kZSkge1xuICAgICAgLy8gICBzY3JpcHQucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChzY3JpcHQpXG4gICAgICAvLyB9XG4gICAgICBsb2FkaW5nU2NyaXB0cy5wdXNoKGV2YWxTY3JpcHQuY2FsbCh0aGlzLCBzY3JpcHQpKTtcbiAgICB9XG4gIH0sIHRoaXMpO1xuXG4gIHJldHVybiBQcm9taXNlLmFsbChsb2FkaW5nU2NyaXB0cyk7XG59XG4iLCIvKiBnbG9iYWwgSFRNTENvbGxlY3Rpb246IHRydWUgKi9cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbHMsIGZuLCBjb250ZXh0KSB7XG4gIGlmIChlbHMgaW5zdGFuY2VvZiBIVE1MQ29sbGVjdGlvbiB8fCBlbHMgaW5zdGFuY2VvZiBOb2RlTGlzdCB8fCBlbHMgaW5zdGFuY2VvZiBBcnJheSkge1xuICAgIHJldHVybiBBcnJheS5wcm90b3R5cGUuZm9yRWFjaC5jYWxsKGVscywgZm4sIGNvbnRleHQpXG4gIH1cbiAgLy8gYXNzdW1lIHNpbXBsZSBkb20gZWxlbWVudFxuICByZXR1cm4gZm4uY2FsbChjb250ZXh0LCBlbHMpXG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2ZvcmVhY2gtZWxzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oc2VsZWN0b3JzLCBjYiwgY29udGV4dCwgRE9NY29udGV4dCkge1xuICBET01jb250ZXh0ID0gRE9NY29udGV4dCB8fCBkb2N1bWVudFxuICBzZWxlY3RvcnMuZm9yRWFjaChmdW5jdGlvbihzZWxlY3Rvcikge1xuICAgIGZvckVhY2hFbHMoRE9NY29udGV4dC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKSwgY2IsIGNvbnRleHQpXG4gIH0pXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKCkge1xuICAvLyBCb3Jyb3dlZCB3aG9sZXNhbGUgZnJvbSBodHRwczovL2dpdGh1Yi5jb20vZGVmdW5rdC9qcXVlcnktcGpheFxuICByZXR1cm4gd2luZG93Lmhpc3RvcnkgJiZcbiAgICB3aW5kb3cuaGlzdG9yeS5wdXNoU3RhdGUgJiZcbiAgICB3aW5kb3cuaGlzdG9yeS5yZXBsYWNlU3RhdGUgJiZcbiAgICAvLyBwdXNoU3RhdGUgaXNu4oCZdCByZWxpYWJsZSBvbiBpT1MgdW50aWwgNS5cbiAgICAhbmF2aWdhdG9yLnVzZXJBZ2VudC5tYXRjaCgvKChpUG9kfGlQaG9uZXxpUGFkKS4rXFxiT1NcXHMrWzEtNF1cXER8V2ViQXBwc1xcLy4rQ0ZOZXR3b3JrKS8pXG59XG4iLCJpZiAoIUZ1bmN0aW9uLnByb3RvdHlwZS5iaW5kKSB7XG4gIEZ1bmN0aW9uLnByb3RvdHlwZS5iaW5kID0gZnVuY3Rpb24ob1RoaXMpIHtcbiAgICBpZiAodHlwZW9mIHRoaXMgIT09IFwiZnVuY3Rpb25cIikge1xuICAgICAgLy8gY2xvc2VzdCB0aGluZyBwb3NzaWJsZSB0byB0aGUgRUNNQVNjcmlwdCA1IGludGVybmFsIElzQ2FsbGFibGUgZnVuY3Rpb25cbiAgICAgIHRocm93IG5ldyBUeXBlRXJyb3IoXCJGdW5jdGlvbi5wcm90b3R5cGUuYmluZCAtIHdoYXQgaXMgdHJ5aW5nIHRvIGJlIGJvdW5kIGlzIG5vdCBjYWxsYWJsZVwiKVxuICAgIH1cblxuICAgIHZhciBhQXJncyA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cywgMSlcbiAgICB2YXIgdGhhdCA9IHRoaXNcbiAgICB2YXIgRm5vb3AgPSBmdW5jdGlvbigpIHt9XG4gICAgdmFyIGZCb3VuZCA9IGZ1bmN0aW9uKCkge1xuICAgICAgcmV0dXJuIHRoYXQuYXBwbHkodGhpcyBpbnN0YW5jZW9mIEZub29wICYmIG9UaGlzID8gdGhpcyA6IG9UaGlzLCBhQXJncy5jb25jYXQoQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXJndW1lbnRzKSkpXG4gICAgfVxuXG4gICAgRm5vb3AucHJvdG90eXBlID0gdGhpcy5wcm90b3R5cGVcbiAgICBmQm91bmQucHJvdG90eXBlID0gbmV3IEZub29wKClcblxuICAgIHJldHVybiBmQm91bmRcbiAgfVxufVxuIiwicmVxdWlyZShcIi4uL3BvbHlmaWxscy9GdW5jdGlvbi5wcm90b3R5cGUuYmluZFwiKVxuXG52YXIgb24gPSByZXF1aXJlKFwiLi4vZXZlbnRzL29uXCIpXG52YXIgY2xvbmUgPSByZXF1aXJlKFwiLi4vY2xvbmVcIilcblxudmFyIGF0dHJDbGljayA9IFwiZGF0YS1wamF4LWNsaWNrLXN0YXRlXCJcblxudmFyIGZvcm1BY3Rpb24gPSBmdW5jdGlvbihlbCwgZXZlbnQpe1xuXG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucyA9IHtcbiAgICByZXF1ZXN0VXJsIDogZWwuZ2V0QXR0cmlidXRlKCdhY3Rpb24nKSB8fCB3aW5kb3cubG9jYXRpb24uaHJlZixcbiAgICByZXF1ZXN0TWV0aG9kIDogZWwuZ2V0QXR0cmlidXRlKCdtZXRob2QnKSB8fCAnR0VUJyxcbiAgfVxuXG4gIC8vY3JlYXRlIGEgdGVzdGFibGUgdmlydHVhbCBsaW5rIG9mIHRoZSBmb3JtIGFjdGlvblxuICB2YXIgdmlydExpbmtFbGVtZW50ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCgnYScpO1xuICB2aXJ0TGlua0VsZW1lbnQuc2V0QXR0cmlidXRlKCdocmVmJywgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zLnJlcXVlc3RVcmwpO1xuXG4gIC8vIElnbm9yZSBleHRlcm5hbCBsaW5rcy5cbiAgaWYgKHZpcnRMaW5rRWxlbWVudC5wcm90b2NvbCAhPT0gd2luZG93LmxvY2F0aW9uLnByb3RvY29sIHx8IHZpcnRMaW5rRWxlbWVudC5ob3N0ICE9PSB3aW5kb3cubG9jYXRpb24uaG9zdCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiZXh0ZXJuYWxcIik7XG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgY2xpY2sgaWYgd2UgYXJlIG9uIGFuIGFuY2hvciBvbiB0aGUgc2FtZSBwYWdlXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQucGF0aG5hbWUgPT09IHdpbmRvdy5sb2NhdGlvbi5wYXRobmFtZSAmJiB2aXJ0TGlua0VsZW1lbnQuaGFzaC5sZW5ndGggPiAwKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItcHJlc2VudFwiKTtcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBlbXB0eSBhbmNob3IgXCJmb28uaHRtbCNcIlxuICBpZiAodmlydExpbmtFbGVtZW50LmhyZWYgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnNwbGl0KFwiI1wiKVswXSArIFwiI1wiKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItZW1wdHlcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIGlmIGRlY2xhcmVkIGFzIGEgZnVsbCByZWxvYWQsIGp1c3Qgbm9ybWFsbHkgc3VibWl0IHRoZSBmb3JtXG4gIGlmICggdGhpcy5vcHRpb25zLmN1cnJlbnRVcmxGdWxsUmVsb2FkKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJyZWxvYWRcIik7XG4gICAgcmV0dXJuO1xuICB9XG5cbiAgZXZlbnQucHJldmVudERlZmF1bHQoKVxuICB2YXIgbmFtZUxpc3QgPSBbXTtcbiAgdmFyIHBhcmFtT2JqZWN0ID0gW107XG4gIGZvcih2YXIgZWxlbWVudEtleSBpbiBlbC5lbGVtZW50cykge1xuICAgIHZhciBlbGVtZW50ID0gZWwuZWxlbWVudHNbZWxlbWVudEtleV07XG4gICAgaWYgKCEhZWxlbWVudC5uYW1lICYmIGVsZW1lbnQuYXR0cmlidXRlcyAhPT0gdW5kZWZpbmVkICYmIGVsZW1lbnQudGFnTmFtZS50b0xvd2VyQ2FzZSgpICE9PSAnYnV0dG9uJyl7XG4gICAgICBpZiAoXG4gICAgICAgIChlbGVtZW50LnR5cGUgIT09ICdjaGVja2JveCcgJiYgZWxlbWVudC50eXBlICE9PSAncmFkaW8nKSB8fCBlbGVtZW50LmNoZWNrZWRcbiAgICAgICkge1xuICAgICAgICBpZihuYW1lTGlzdC5pbmRleE9mKGVsZW1lbnQubmFtZSkgPT09IC0xKXtcbiAgICAgICAgICBuYW1lTGlzdC5wdXNoKGVsZW1lbnQubmFtZSk7XG4gICAgICAgICAgcGFyYW1PYmplY3QucHVzaCh7IG5hbWU6IGVuY29kZVVSSUNvbXBvbmVudChlbGVtZW50Lm5hbWUpLCB2YWx1ZTogZW5jb2RlVVJJQ29tcG9uZW50KGVsZW1lbnQudmFsdWUpfSk7XG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9XG4gIH1cblxuXG5cbiAgLy9DcmVhdGluZyBhIGdldFN0cmluZ1xuICB2YXIgcGFyYW1zU3RyaW5nID0gKHBhcmFtT2JqZWN0Lm1hcChmdW5jdGlvbih2YWx1ZSl7cmV0dXJuIHZhbHVlLm5hbWUrXCI9XCIrdmFsdWUudmFsdWU7fSkpLmpvaW4oJyYnKTtcblxuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFBheWxvYWQgPSBwYXJhbU9iamVjdDtcbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zLnJlcXVlc3RQYXlsb2FkU3RyaW5nID0gcGFyYW1zU3RyaW5nO1xuXG4gIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwic3VibWl0XCIpO1xuXG4gIHRoaXMubG9hZFVybCh2aXJ0TGlua0VsZW1lbnQuaHJlZiwgY2xvbmUodGhpcy5vcHRpb25zKSlcblxufTtcblxudmFyIGlzRGVmYXVsdFByZXZlbnRlZCA9IGZ1bmN0aW9uKGV2ZW50KSB7XG4gIHJldHVybiBldmVudC5kZWZhdWx0UHJldmVudGVkIHx8IGV2ZW50LnJldHVyblZhbHVlID09PSBmYWxzZTtcbn07XG5cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB2YXIgdGhhdCA9IHRoaXNcblxuICBvbihlbCwgXCJzdWJtaXRcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgZm9ybUFjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgfSlcblxuICBvbihlbCwgXCJrZXl1cFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cblxuICAgIGlmIChldmVudC5rZXlDb2RlID09IDEzKSB7XG4gICAgICBmb3JtQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICAgIH1cbiAgfS5iaW5kKHRoaXMpKVxufVxuIiwicmVxdWlyZShcIi4uL3BvbHlmaWxscy9GdW5jdGlvbi5wcm90b3R5cGUuYmluZFwiKVxuXG52YXIgb24gPSByZXF1aXJlKFwiLi4vZXZlbnRzL29uXCIpXG52YXIgY2xvbmUgPSByZXF1aXJlKFwiLi4vY2xvbmVcIilcblxudmFyIGF0dHJDbGljayA9IFwiZGF0YS1wamF4LWNsaWNrLXN0YXRlXCJcbnZhciBhdHRyS2V5ID0gXCJkYXRhLXBqYXgta2V5dXAtc3RhdGVcIlxuXG52YXIgbGlua0FjdGlvbiA9IGZ1bmN0aW9uKGVsLCBldmVudCkge1xuICAvLyBEb27igJl0IGJyZWFrIGJyb3dzZXIgc3BlY2lhbCBiZWhhdmlvciBvbiBsaW5rcyAobGlrZSBwYWdlIGluIG5ldyB3aW5kb3cpXG4gIGlmIChldmVudC53aGljaCA+IDEgfHwgZXZlbnQubWV0YUtleSB8fCBldmVudC5jdHJsS2V5IHx8IGV2ZW50LnNoaWZ0S2V5IHx8IGV2ZW50LmFsdEtleSkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwibW9kaWZpZXJcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIHdlIGRvIHRlc3Qgb24gaHJlZiBub3cgdG8gcHJldmVudCB1bmV4cGVjdGVkIGJlaGF2aW9yIGlmIGZvciBzb21lIHJlYXNvblxuICAvLyB1c2VyIGhhdmUgaHJlZiB0aGF0IGNhbiBiZSBkeW5hbWljYWxseSB1cGRhdGVkXG5cbiAgLy8gSWdub3JlIGV4dGVybmFsIGxpbmtzLlxuICBpZiAoZWwucHJvdG9jb2wgIT09IHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCB8fCBlbC5ob3N0ICE9PSB3aW5kb3cubG9jYXRpb24uaG9zdCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiZXh0ZXJuYWxcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBjbGljayBpZiB3ZSBhcmUgb24gYW4gYW5jaG9yIG9uIHRoZSBzYW1lIHBhZ2VcbiAgaWYgKGVsLnBhdGhuYW1lID09PSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgJiYgZWwuaGFzaC5sZW5ndGggPiAwKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItcHJlc2VudFwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGFuY2hvcnMgb24gdGhlIHNhbWUgcGFnZSAoa2VlcCBuYXRpdmUgYmVoYXZpb3IpXG4gIGlmIChlbC5oYXNoICYmIGVsLmhyZWYucmVwbGFjZShlbC5oYXNoLCBcIlwiKSA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYucmVwbGFjZShsb2NhdGlvbi5oYXNoLCBcIlwiKSkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yXCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgZW1wdHkgYW5jaG9yIFwiZm9vLmh0bWwjXCJcbiAgaWYgKGVsLmhyZWYgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnNwbGl0KFwiI1wiKVswXSArIFwiI1wiKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItZW1wdHlcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIGV2ZW50LnByZXZlbnREZWZhdWx0KClcblxuICAvLyBkb27igJl0IGRvIFwibm90aGluZ1wiIGlmIHVzZXIgdHJ5IHRvIHJlbG9hZCB0aGUgcGFnZSBieSBjbGlja2luZyB0aGUgc2FtZSBsaW5rIHR3aWNlXG4gIGlmIChcbiAgICB0aGlzLm9wdGlvbnMuY3VycmVudFVybEZ1bGxSZWxvYWQgJiZcbiAgICBlbC5ocmVmID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5zcGxpdChcIiNcIilbMF1cbiAgKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJyZWxvYWRcIilcbiAgICB0aGlzLnJlbG9hZCgpXG4gICAgcmV0dXJuXG4gIH1cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zID0gdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zIHx8IHt9O1xuICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImxvYWRcIilcbiAgdGhpcy5sb2FkVXJsKGVsLmhyZWYsIGNsb25lKHRoaXMub3B0aW9ucykpXG59XG5cbnZhciBpc0RlZmF1bHRQcmV2ZW50ZWQgPSBmdW5jdGlvbihldmVudCkge1xuICByZXR1cm4gZXZlbnQuZGVmYXVsdFByZXZlbnRlZCB8fCBldmVudC5yZXR1cm5WYWx1ZSA9PT0gZmFsc2U7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgdmFyIHRoYXQgPSB0aGlzXG5cbiAgb24oZWwsIFwiY2xpY2tcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgbGlua0FjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgfSlcblxuICBvbihlbCwgXCJrZXl1cFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICAvLyBEb27igJl0IGJyZWFrIGJyb3dzZXIgc3BlY2lhbCBiZWhhdmlvciBvbiBsaW5rcyAobGlrZSBwYWdlIGluIG5ldyB3aW5kb3cpXG4gICAgaWYgKGV2ZW50LndoaWNoID4gMSB8fCBldmVudC5tZXRhS2V5IHx8IGV2ZW50LmN0cmxLZXkgfHwgZXZlbnQuc2hpZnRLZXkgfHwgZXZlbnQuYWx0S2V5KSB7XG4gICAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0cktleSwgXCJtb2RpZmllclwiKVxuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgaWYgKGV2ZW50LmtleUNvZGUgPT0gMTMpIHtcbiAgICAgIGxpbmtBY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gICAgfVxuICB9LmJpbmQodGhpcykpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHJldHVybiBlbC5xdWVyeVNlbGVjdG9yQWxsKHRoaXMub3B0aW9ucy5lbGVtZW50cylcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oKSB7XG4gIGlmICgodGhpcy5vcHRpb25zLmRlYnVnICYmIGNvbnNvbGUpKSB7XG4gICAgaWYgKHR5cGVvZiBjb25zb2xlLmxvZyA9PT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICBjb25zb2xlLmxvZy5hcHBseShjb25zb2xlLCBhcmd1bWVudHMpO1xuICAgIH1cbiAgICAvLyBpZSBpcyB3ZWlyZFxuICAgIGVsc2UgaWYgKGNvbnNvbGUubG9nKSB7XG4gICAgICBjb25zb2xlLmxvZyhhcmd1bWVudHMpO1xuICAgIH1cbiAgfVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxudmFyIHBhcnNlRWxlbWVudCA9IHJlcXVpcmUoXCIuL3BhcnNlLWVsZW1lbnRcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICBmb3JFYWNoRWxzKHRoaXMuZ2V0RWxlbWVudHMoZWwpLCBwYXJzZUVsZW1lbnQsIHRoaXMpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHN3aXRjaCAoZWwudGFnTmFtZS50b0xvd2VyQ2FzZSgpKSB7XG4gIGNhc2UgXCJhXCI6XG4gICAgLy8gb25seSBhdHRhY2ggbGluayBpZiBlbCBkb2VzIG5vdCBhbHJlYWR5IGhhdmUgbGluayBhdHRhY2hlZFxuICAgIGlmICghZWwuaGFzQXR0cmlidXRlKCdkYXRhLXBqYXgtY2xpY2stc3RhdGUnKSkge1xuICAgICAgdGhpcy5hdHRhY2hMaW5rKGVsKVxuICAgIH1cbiAgICBicmVha1xuXG4gICAgY2FzZSBcImZvcm1cIjpcbiAgICAgIC8vIG9ubHkgYXR0YWNoIGxpbmsgaWYgZWwgZG9lcyBub3QgYWxyZWFkeSBoYXZlIGxpbmsgYXR0YWNoZWRcbiAgICAgIGlmICghZWwuaGFzQXR0cmlidXRlKCdkYXRhLXBqYXgtY2xpY2stc3RhdGUnKSkge1xuICAgICAgICB0aGlzLmF0dGFjaEZvcm0oZWwpXG4gICAgICB9XG4gICAgYnJlYWtcblxuICBkZWZhdWx0OlxuICAgIHRocm93IFwiUGpheCBjYW4gb25seSBiZSBhcHBsaWVkIG9uIDxhPiBvciA8Zm9ybT4gc3VibWl0XCJcbiAgfVxufVxuIiwiLyogZ2xvYmFsIF9nYXE6IHRydWUsIGdhOiB0cnVlICovXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24ob3B0aW9ucyl7XG4gIHRoaXMub3B0aW9ucyA9IG9wdGlvbnNcbiAgdGhpcy5vcHRpb25zLmVsZW1lbnRzID0gdGhpcy5vcHRpb25zLmVsZW1lbnRzIHx8IFwiYVtocmVmXSwgZm9ybVthY3Rpb25dXCIsXG4gIHRoaXMub3B0aW9ucy5yZVJlbmRlckNTUyA9IHRoaXMub3B0aW9ucy5yZVJlbmRlckNTUyB8fCBmYWxzZSxcbiAgdGhpcy5vcHRpb25zLm1haW5TY3JpcHRFbGVtZW50ID0gdGhpcy5vcHRpb25zLm1haW5TY3JpcHRFbGVtZW50IHx8IFwiaGVhZFwiXG4gIHRoaXMub3B0aW9ucy5zZWxlY3RvcnMgPSB0aGlzLm9wdGlvbnMuc2VsZWN0b3JzIHx8IFtcInRpdGxlXCIsIFwiLmpzLVBqYXhcIl1cbiAgdGhpcy5vcHRpb25zLnN3aXRjaGVzID0gdGhpcy5vcHRpb25zLnN3aXRjaGVzIHx8IHt9XG4gIHRoaXMub3B0aW9ucy5zd2l0Y2hlc09wdGlvbnMgPSB0aGlzLm9wdGlvbnMuc3dpdGNoZXNPcHRpb25zIHx8IHt9XG4gIHRoaXMub3B0aW9ucy5oaXN0b3J5ID0gdGhpcy5vcHRpb25zLmhpc3RvcnkgfHwgdHJ1ZVxuICB0aGlzLm9wdGlvbnMuYW5hbHl0aWNzID0gdGhpcy5vcHRpb25zLmFuYWx5dGljcyB8fCBmdW5jdGlvbigpIHtcbiAgICAvLyBvcHRpb25zLmJhY2t3YXJkIG9yIG9wdGlvbnMuZm93YXJkIGNhbiBiZSB0cnVlIG9yIHVuZGVmaW5lZFxuICAgIC8vIGJ5IGRlZmF1bHQsIHdlIGRvIHRyYWNrIGJhY2svZm93YXJkIGhpdFxuICAgIC8vIGh0dHBzOi8vcHJvZHVjdGZvcnVtcy5nb29nbGUuY29tL2ZvcnVtLyMhdG9waWMvYW5hbHl0aWNzL1dWd01EakxoWFlrXG4gICAgaWYgKHdpbmRvdy5fZ2FxKSB7XG4gICAgICBfZ2FxLnB1c2goW1wiX3RyYWNrUGFnZXZpZXdcIl0pXG4gICAgfVxuICAgIGlmICh3aW5kb3cuZ2EpIHtcbiAgICAgIGdhKFwic2VuZFwiLCBcInBhZ2V2aWV3XCIsIHtwYWdlOiBsb2NhdGlvbi5wYXRobmFtZSwgdGl0bGU6IGRvY3VtZW50LnRpdGxlfSlcbiAgICB9XG4gIH1cbiAgdGhpcy5vcHRpb25zLnNjcm9sbFRvID0gKHR5cGVvZiB0aGlzLm9wdGlvbnMuc2Nyb2xsVG8gPT09ICd1bmRlZmluZWQnKSA/IDAgOiB0aGlzLm9wdGlvbnMuc2Nyb2xsVG87XG4gIHRoaXMub3B0aW9ucy5jYWNoZUJ1c3QgPSAodHlwZW9mIHRoaXMub3B0aW9ucy5jYWNoZUJ1c3QgPT09ICd1bmRlZmluZWQnKSA/IHRydWUgOiB0aGlzLm9wdGlvbnMuY2FjaGVCdXN0XG4gIHRoaXMub3B0aW9ucy5kZWJ1ZyA9IHRoaXMub3B0aW9ucy5kZWJ1ZyB8fCBmYWxzZVxuXG4gIC8vIHdlIGNhbuKAmXQgcmVwbGFjZSBib2R5Lm91dGVySFRNTCBvciBoZWFkLm91dGVySFRNTFxuICAvLyBpdCBjcmVhdGUgYSBidWcgd2hlcmUgbmV3IGJvZHkgb3IgbmV3IGhlYWQgYXJlIGNyZWF0ZWQgaW4gdGhlIGRvbVxuICAvLyBpZiB5b3Ugc2V0IGhlYWQub3V0ZXJIVE1MLCBhIG5ldyBib2R5IHRhZyBpcyBhcHBlbmRlZCwgc28gdGhlIGRvbSBnZXQgMiBib2R5XG4gIC8vICYgaXQgYnJlYWsgdGhlIHN3aXRjaEZhbGxiYWNrIHdoaWNoIHJlcGxhY2UgaGVhZCAmIGJvZHlcbiAgaWYgKCF0aGlzLm9wdGlvbnMuc3dpdGNoZXMuaGVhZCkge1xuICAgIHRoaXMub3B0aW9ucy5zd2l0Y2hlcy5oZWFkID0gdGhpcy5zd2l0Y2hFbGVtZW50c0FsdFxuICB9XG4gIGlmICghdGhpcy5vcHRpb25zLnN3aXRjaGVzLmJvZHkpIHtcbiAgICB0aGlzLm9wdGlvbnMuc3dpdGNoZXMuYm9keSA9IHRoaXMuc3dpdGNoRWxlbWVudHNBbHRcbiAgfVxuICBpZiAodHlwZW9mIG9wdGlvbnMuYW5hbHl0aWNzICE9PSBcImZ1bmN0aW9uXCIpIHtcbiAgICBvcHRpb25zLmFuYWx5dGljcyA9IGZ1bmN0aW9uKCkge31cbiAgfVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB0aGlzLnBhcnNlRE9NKGVsIHx8IGRvY3VtZW50KVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbigpIHtcbiAgd2luZG93LmxvY2F0aW9uLnJlbG9hZCgpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGxvY2F0aW9uLCBvcHRpb25zLCBjYWxsYmFjaykge1xuICBvcHRpb25zID0gb3B0aW9ucyB8fCB7fTtcbiAgdmFyIHJlcXVlc3RNZXRob2QgPSBvcHRpb25zLnJlcXVlc3RNZXRob2QgfHwgXCJHRVRcIjtcbiAgdmFyIHJlcXVlc3RQYXlsb2FkID0gb3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyB8fCBudWxsO1xuICB2YXIgcmVxdWVzdCA9IG5ldyBYTUxIdHRwUmVxdWVzdCgpXG5cbiAgcmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSBmdW5jdGlvbigpIHtcbiAgICBpZiAocmVxdWVzdC5yZWFkeVN0YXRlID09PSA0KSB7XG4gICAgICBpZiAocmVxdWVzdC5zdGF0dXMgPT09IDIwMCkge1xuICAgICAgICBjYWxsYmFjayhyZXF1ZXN0LnJlc3BvbnNlVGV4dCwgcmVxdWVzdClcbiAgICAgIH1cbiAgICAgIGVsc2Uge1xuICAgICAgICBjYWxsYmFjayhudWxsLCByZXF1ZXN0KVxuICAgICAgfVxuICAgIH1cbiAgfVxuXG4gIC8vIEFkZCBhIHRpbWVzdGFtcCBhcyBwYXJ0IG9mIHRoZSBxdWVyeSBzdHJpbmcgaWYgY2FjaGUgYnVzdGluZyBpcyBlbmFibGVkXG4gIGlmICh0aGlzLm9wdGlvbnMuY2FjaGVCdXN0KSB7XG4gICAgbG9jYXRpb24gKz0gKCEvWz8mXS8udGVzdChsb2NhdGlvbikgPyBcIj9cIiA6IFwiJlwiKSArIG5ldyBEYXRlKCkuZ2V0VGltZSgpXG4gIH1cblxuICByZXF1ZXN0Lm9wZW4ocmVxdWVzdE1ldGhvZC50b1VwcGVyQ2FzZSgpLCBsb2NhdGlvbiwgdHJ1ZSlcbiAgcmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKFwiWC1SZXF1ZXN0ZWQtV2l0aFwiLCBcIlhNTEh0dHBSZXF1ZXN0XCIpXG5cbiAgLy8gQWRkIHRoZSByZXF1ZXN0IHBheWxvYWQgaWYgYXZhaWxhYmxlXG4gIGlmIChvcHRpb25zLnJlcXVlc3RQYXlsb2FkU3RyaW5nICE9IHVuZGVmaW5lZCAmJiBvcHRpb25zLnJlcXVlc3RQYXlsb2FkU3RyaW5nICE9IFwiXCIpIHtcbiAgICAvLyBTZW5kIHRoZSBwcm9wZXIgaGVhZGVyIGluZm9ybWF0aW9uIGFsb25nIHdpdGggdGhlIHJlcXVlc3RcbiAgICByZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoXCJDb250ZW50LXR5cGVcIiwgXCJhcHBsaWNhdGlvbi94LXd3dy1mb3JtLXVybGVuY29kZWRcIik7XG4gIH1cblxuICByZXF1ZXN0LnNlbmQocmVxdWVzdFBheWxvYWQpXG5cbiAgcmV0dXJuIHJlcXVlc3Rcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcblxudmFyIGRlZmF1bHRTd2l0Y2hlcyA9IHJlcXVpcmUoXCIuL3N3aXRjaGVzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oc3dpdGNoZXMsIHN3aXRjaGVzT3B0aW9ucywgc2VsZWN0b3JzLCBmcm9tRWwsIHRvRWwsIG9wdGlvbnMpIHtcbiAgc2VsZWN0b3JzLmZvckVhY2goZnVuY3Rpb24oc2VsZWN0b3IpIHtcbiAgICB2YXIgbmV3RWxzID0gZnJvbUVsLnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpXG4gICAgdmFyIG9sZEVscyA9IHRvRWwucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcilcbiAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgIHRoaXMubG9nKFwiUGpheCBzd2l0Y2hcIiwgc2VsZWN0b3IsIG5ld0Vscywgb2xkRWxzKVxuICAgIH1cbiAgICBpZiAobmV3RWxzLmxlbmd0aCAhPT0gb2xkRWxzLmxlbmd0aCkge1xuICAgICAgLy8gZm9yRWFjaEVscyhuZXdFbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICAvLyAgIHRoaXMubG9nKFwibmV3RWxcIiwgZWwsIGVsLm91dGVySFRNTClcbiAgICAgIC8vIH0sIHRoaXMpXG4gICAgICAvLyBmb3JFYWNoRWxzKG9sZEVscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIC8vICAgdGhpcy5sb2coXCJvbGRFbFwiLCBlbCwgZWwub3V0ZXJIVE1MKVxuICAgICAgLy8gfSwgdGhpcylcbiAgICAgIHRocm93IFwiRE9NIGRvZXNu4oCZdCBsb29rIHRoZSBzYW1lIG9uIG5ldyBsb2FkZWQgcGFnZTog4oCZXCIgKyBzZWxlY3RvciArIFwi4oCZIC0gbmV3IFwiICsgbmV3RWxzLmxlbmd0aCArIFwiLCBvbGQgXCIgKyBvbGRFbHMubGVuZ3RoXG4gICAgfVxuXG4gICAgZm9yRWFjaEVscyhuZXdFbHMsIGZ1bmN0aW9uKG5ld0VsLCBpKSB7XG4gICAgICB2YXIgb2xkRWwgPSBvbGRFbHNbaV1cbiAgICAgIGlmICh0aGlzLmxvZykge1xuICAgICAgICB0aGlzLmxvZyhcIm5ld0VsXCIsIG5ld0VsLCBcIm9sZEVsXCIsIG9sZEVsKVxuICAgICAgfVxuICAgICAgaWYgKHN3aXRjaGVzW3NlbGVjdG9yXSkge1xuICAgICAgICBzd2l0Y2hlc1tzZWxlY3Rvcl0uYmluZCh0aGlzKShvbGRFbCwgbmV3RWwsIG9wdGlvbnMsIHN3aXRjaGVzT3B0aW9uc1tzZWxlY3Rvcl0pXG4gICAgICB9XG4gICAgICBlbHNlIHtcbiAgICAgICAgZGVmYXVsdFN3aXRjaGVzLm91dGVySFRNTC5iaW5kKHRoaXMpKG9sZEVsLCBuZXdFbCwgb3B0aW9ucylcbiAgICAgIH1cbiAgICB9LCB0aGlzKVxuICB9LCB0aGlzKVxufVxuIiwidmFyIG9uID0gcmVxdWlyZShcIi4vZXZlbnRzL29uLmpzXCIpXG4vLyB2YXIgb2ZmID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy9vbi5qc1wiKVxuLy8gdmFyIHRyaWdnZXIgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL3RyaWdnZXIuanNcIilcblxuXG5tb2R1bGUuZXhwb3J0cyA9IHtcbiAgb3V0ZXJIVE1MOiBmdW5jdGlvbihvbGRFbCwgbmV3RWwpIHtcbiAgICBvbGRFbC5vdXRlckhUTUwgPSBuZXdFbC5vdXRlckhUTUxcbiAgICB0aGlzLm9uU3dpdGNoKClcbiAgfSxcblxuICBpbm5lckhUTUw6IGZ1bmN0aW9uKG9sZEVsLCBuZXdFbCkge1xuICAgIG9sZEVsLmlubmVySFRNTCA9IG5ld0VsLmlubmVySFRNTFxuICAgIG9sZEVsLmNsYXNzTmFtZSA9IG5ld0VsLmNsYXNzTmFtZVxuICAgIHRoaXMub25Td2l0Y2goKVxuICB9LFxuXG4gIHNpZGVCeVNpZGU6IGZ1bmN0aW9uKG9sZEVsLCBuZXdFbCwgb3B0aW9ucywgc3dpdGNoT3B0aW9ucykge1xuICAgIHZhciBmb3JFYWNoID0gQXJyYXkucHJvdG90eXBlLmZvckVhY2hcbiAgICB2YXIgZWxzVG9SZW1vdmUgPSBbXVxuICAgIHZhciBlbHNUb0FkZCA9IFtdXG4gICAgdmFyIGZyYWdUb0FwcGVuZCA9IGRvY3VtZW50LmNyZWF0ZURvY3VtZW50RnJhZ21lbnQoKVxuICAgIC8vIGhlaWdodCB0cmFuc2l0aW9uIGFyZSBzaGl0dHkgb24gc2FmYXJpXG4gICAgLy8gc28gY29tbWVudGVkIGZvciBub3cgKHVudGlsIEkgZm91bmQgc29tZXRoaW5nID8pXG4gICAgLy8gdmFyIHJlbGV2YW50SGVpZ2h0ID0gMFxuICAgIHZhciBhbmltYXRpb25FdmVudE5hbWVzID0gXCJhbmltYXRpb25lbmQgd2Via2l0QW5pbWF0aW9uRW5kIE1TQW5pbWF0aW9uRW5kIG9hbmltYXRpb25lbmRcIlxuICAgIHZhciBhbmltYXRlZEVsc051bWJlciA9IDBcbiAgICB2YXIgc2V4eUFuaW1hdGlvbkVuZCA9IGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgICBpZiAoZS50YXJnZXQgIT0gZS5jdXJyZW50VGFyZ2V0KSB7XG4gICAgICAgICAgICAvLyBlbmQgdHJpZ2dlcmVkIGJ5IGFuIGFuaW1hdGlvbiBvbiBhIGNoaWxkXG4gICAgICAgICAgICByZXR1cm5cbiAgICAgICAgICB9XG5cbiAgICAgICAgICBhbmltYXRlZEVsc051bWJlci0tXG4gICAgICAgICAgaWYgKGFuaW1hdGVkRWxzTnVtYmVyIDw9IDAgJiYgZWxzVG9SZW1vdmUpIHtcbiAgICAgICAgICAgIGVsc1RvUmVtb3ZlLmZvckVhY2goZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgICAgICAgLy8gYnJvd3NpbmcgcXVpY2tseSBjYW4gbWFrZSB0aGUgZWxcbiAgICAgICAgICAgICAgLy8gYWxyZWFkeSByZW1vdmVkIGJ5IGxhc3QgcGFnZSB1cGRhdGUgP1xuICAgICAgICAgICAgICBpZiAoZWwucGFyZW50Tm9kZSkge1xuICAgICAgICAgICAgICAgIGVsLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoZWwpXG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgIGVsc1RvQWRkLmZvckVhY2goZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgICAgICAgZWwuY2xhc3NOYW1lID0gZWwuY2xhc3NOYW1lLnJlcGxhY2UoZWwuZ2V0QXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIiksIFwiXCIpXG4gICAgICAgICAgICAgIGVsLnJlbW92ZUF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpXG4gICAgICAgICAgICAgIC8vIFBqYXgub2ZmKGVsLCBhbmltYXRpb25FdmVudE5hbWVzLCBzZXh5QW5pbWF0aW9uRW5kLCB0cnVlKVxuICAgICAgICAgICAgfSlcblxuICAgICAgICAgICAgZWxzVG9BZGQgPSBudWxsIC8vIGZyZWUgbWVtb3J5XG4gICAgICAgICAgICBlbHNUb1JlbW92ZSA9IG51bGwgLy8gZnJlZSBtZW1vcnlcblxuICAgICAgICAgICAgLy8gYXNzdW1lIHRoZSBoZWlnaHQgaXMgbm93IHVzZWxlc3MgKGF2b2lkIGJ1ZyBzaW5jZSB0aGVyZSBpcyBvdmVyZmxvdyBoaWRkZW4gb24gdGhlIHBhcmVudClcbiAgICAgICAgICAgIC8vIG9sZEVsLnN0eWxlLmhlaWdodCA9IFwiYXV0b1wiXG5cbiAgICAgICAgICAgIC8vIHRoaXMgaXMgdG8gdHJpZ2dlciBzb21lIHJlcGFpbnQgKGV4YW1wbGU6IHBpY3R1cmVmaWxsKVxuICAgICAgICAgICAgdGhpcy5vblN3aXRjaCgpXG4gICAgICAgICAgICAvLyBQamF4LnRyaWdnZXIod2luZG93LCBcInNjcm9sbFwiKVxuICAgICAgICAgIH1cbiAgICAgICAgfS5iaW5kKHRoaXMpXG5cbiAgICAvLyBGb3JjZSBoZWlnaHQgdG8gYmUgYWJsZSB0byB0cmlnZ2VyIGNzcyBhbmltYXRpb25cbiAgICAvLyBoZXJlIHdlIGdldCB0aGUgcmVsZXZhbnQgaGVpZ2h0XG4gICAgLy8gb2xkRWwucGFyZW50Tm9kZS5hcHBlbmRDaGlsZChuZXdFbClcbiAgICAvLyByZWxldmFudEhlaWdodCA9IG5ld0VsLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpLmhlaWdodFxuICAgIC8vIG9sZEVsLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQobmV3RWwpXG4gICAgLy8gb2xkRWwuc3R5bGUuaGVpZ2h0ID0gb2xkRWwuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0ICsgXCJweFwiXG5cbiAgICBzd2l0Y2hPcHRpb25zID0gc3dpdGNoT3B0aW9ucyB8fCB7fVxuXG4gICAgZm9yRWFjaC5jYWxsKG9sZEVsLmNoaWxkTm9kZXMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICBlbHNUb1JlbW92ZS5wdXNoKGVsKVxuICAgICAgaWYgKGVsLmNsYXNzTGlzdCAmJiAhZWwuY2xhc3NMaXN0LmNvbnRhaW5zKFwianMtUGpheC1yZW1vdmVcIikpIHtcbiAgICAgICAgLy8gZm9yIGZhc3Qgc3dpdGNoLCBjbGVhbiBlbGVtZW50IHRoYXQganVzdCBoYXZlIGJlZW4gYWRkZWQsICYgbm90IGNsZWFuZWQgeWV0LlxuICAgICAgICBpZiAoZWwuaGFzQXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIikpIHtcbiAgICAgICAgICBlbC5jbGFzc05hbWUgPSBlbC5jbGFzc05hbWUucmVwbGFjZShlbC5nZXRBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKSwgXCJcIilcbiAgICAgICAgICBlbC5yZW1vdmVBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKVxuICAgICAgICB9XG4gICAgICAgIGVsLmNsYXNzTGlzdC5hZGQoXCJqcy1QamF4LXJlbW92ZVwiKVxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MgJiYgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MucmVtb3ZlRWxlbWVudCkge1xuICAgICAgICAgIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLnJlbW92ZUVsZW1lbnQoZWwpXG4gICAgICAgIH1cbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcykge1xuICAgICAgICAgIGVsLmNsYXNzTmFtZSArPSBcIiBcIiArIHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5yZW1vdmUgKyBcIiBcIiArIChvcHRpb25zLmJhY2t3YXJkID8gc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmJhY2t3YXJkIDogc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmZvcndhcmQpXG4gICAgICAgIH1cbiAgICAgICAgYW5pbWF0ZWRFbHNOdW1iZXIrK1xuICAgICAgICBvbihlbCwgYW5pbWF0aW9uRXZlbnROYW1lcywgc2V4eUFuaW1hdGlvbkVuZCwgdHJ1ZSlcbiAgICAgIH1cbiAgICB9KVxuXG4gICAgZm9yRWFjaC5jYWxsKG5ld0VsLmNoaWxkTm9kZXMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICBpZiAoZWwuY2xhc3NMaXN0KSB7XG4gICAgICAgIHZhciBhZGRDbGFzc2VzID0gXCJcIlxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzKSB7XG4gICAgICAgICAgYWRkQ2xhc3NlcyA9IFwiIGpzLVBqYXgtYWRkIFwiICsgc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmFkZCArIFwiIFwiICsgKG9wdGlvbnMuYmFja3dhcmQgPyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuZm9yd2FyZCA6IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5iYWNrd2FyZClcbiAgICAgICAgfVxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MgJiYgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MuYWRkRWxlbWVudCkge1xuICAgICAgICAgIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLmFkZEVsZW1lbnQoZWwpXG4gICAgICAgIH1cbiAgICAgICAgZWwuY2xhc3NOYW1lICs9IGFkZENsYXNzZXNcbiAgICAgICAgZWwuc2V0QXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIiwgYWRkQ2xhc3NlcylcbiAgICAgICAgZWxzVG9BZGQucHVzaChlbClcbiAgICAgICAgZnJhZ1RvQXBwZW5kLmFwcGVuZENoaWxkKGVsKVxuICAgICAgICBhbmltYXRlZEVsc051bWJlcisrXG4gICAgICAgIG9uKGVsLCBhbmltYXRpb25FdmVudE5hbWVzLCBzZXh5QW5pbWF0aW9uRW5kLCB0cnVlKVxuICAgICAgfVxuICAgIH0pXG5cbiAgICAvLyBwYXNzIGFsbCBjbGFzc05hbWUgb2YgdGhlIHBhcmVudFxuICAgIG9sZEVsLmNsYXNzTmFtZSA9IG5ld0VsLmNsYXNzTmFtZVxuICAgIG9sZEVsLmFwcGVuZENoaWxkKGZyYWdUb0FwcGVuZClcblxuICAgIC8vIG9sZEVsLnN0eWxlLmhlaWdodCA9IHJlbGV2YW50SGVpZ2h0ICsgXCJweFwiXG4gIH1cbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gKGZ1bmN0aW9uKCkge1xuICB2YXIgY291bnRlciA9IDBcbiAgcmV0dXJuIGZ1bmN0aW9uKCkge1xuICAgIHZhciBpZCA9IChcInBqYXhcIiArIChuZXcgRGF0ZSgpLmdldFRpbWUoKSkpICsgXCJfXCIgKyBjb3VudGVyXG4gICAgY291bnRlcisrXG4gICAgcmV0dXJuIGlkXG4gIH1cbn0pKClcbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbGVtZW50cywgb2xkRWxlbWVudHMpIHtcbiAgIHRoaXMubG9nKFwic3R5bGVoZWV0cyBvbGQgZWxlbWVudHNcIiwgb2xkRWxlbWVudHMpO1xuICAgdGhpcy5sb2coXCJzdHlsZWhlZXRzIG5ldyBlbGVtZW50c1wiLCBlbGVtZW50cyk7XG4gIHZhciB0b0FycmF5ID0gZnVuY3Rpb24oZW51bWVyYWJsZSl7XG4gICAgICB2YXIgYXJyID0gW107XG4gICAgICBmb3IodmFyIGkgPSBlbnVtZXJhYmxlLmxlbmd0aDsgaS0tOyBhcnIudW5zaGlmdChlbnVtZXJhYmxlW2ldKSk7XG4gICAgICByZXR1cm4gYXJyO1xuICB9O1xuICBmb3JFYWNoRWxzKGVsZW1lbnRzLCBmdW5jdGlvbihuZXdFbCwgaSkge1xuICAgIHZhciBvbGRFbGVtZW50c0FycmF5ID0gdG9BcnJheShvbGRFbGVtZW50cyk7XG4gICAgdmFyIHJlc2VtYmxpbmdPbGQgPSBvbGRFbGVtZW50c0FycmF5LnJlZHVjZShmdW5jdGlvbihhY2MsIG9sZEVsKXsgXG4gICAgICBhY2MgPSAoKG9sZEVsLmhyZWYgPT09IG5ld0VsLmhyZWYpID8gb2xkRWwgOiBhY2MpOyAgXG4gICAgICByZXR1cm4gYWNjO1xuICAgIH0sIG51bGwpO1xuXG4gICAgaWYocmVzZW1ibGluZ09sZCAhPT0gbnVsbCl7XG4gICAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgICAgdGhpcy5sb2coXCJvbGQgc3R5bGVzaGVldCBmb3VuZCBub3QgcmVzZXR0aW5nXCIpO1xuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgICAgdGhpcy5sb2coXCJuZXcgc3R5bGVzaGVldCA9PiBhZGQgdG8gaGVhZFwiKTtcbiAgICAgIH1cbiAgICAgIHZhciBoZWFkID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoICdoZWFkJyApWzBdLCBcbiAgICAgICBsaW5rID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCggJ2xpbmsnICk7ICAgICAgICAgICBcbiAgICAgICAgbGluay5zZXRBdHRyaWJ1dGUoICdocmVmJywgbmV3RWwuaHJlZiApO1xuICAgICAgICBsaW5rLnNldEF0dHJpYnV0ZSggJ3JlbCcsICdzdHlsZXNoZWV0JyApO1xuICAgICAgICBsaW5rLnNldEF0dHJpYnV0ZSggJ3R5cGUnLCAndGV4dC9jc3MnICk7XG4gICAgfVxuICB9LCB0aGlzKTtcblxufVxuIl19
