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

},{"./lib/clone.js":2,"./lib/events/on.js":4,"./lib/events/trigger.js":5,"./lib/execute-scripts.js":6,"./lib/foreach-els.js":7,"./lib/foreach-selectors.js":8,"./lib/is-supported.js":9,"./lib/proto/attach-form.js":11,"./lib/proto/attach-link.js":12,"./lib/proto/get-elements.js":13,"./lib/proto/log.js":14,"./lib/proto/parse-dom.js":15,"./lib/proto/parse-options.js":17,"./lib/proto/refresh.js":18,"./lib/reload.js":19,"./lib/request.js":20,"./lib/switches-selectors.js":21,"./lib/uniqueid.js":23}],2:[function(require,module,exports){
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
  if ((this.options.debug && console) || 1===1) {
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
  this.options.elements = this.options.elements || "a[href], form[action]"
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

},{}]},{},[1])(1)
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi4uLy4uL25vZGUvbGliL25vZGVfbW9kdWxlcy9icm93c2VyaWZ5L25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJpbmRleC5qcyIsImxpYi9jbG9uZS5qcyIsImxpYi9ldmFsLXNjcmlwdC5qcyIsImxpYi9ldmVudHMvb24uanMiLCJsaWIvZXZlbnRzL3RyaWdnZXIuanMiLCJsaWIvZXhlY3V0ZS1zY3JpcHRzLmpzIiwibGliL2ZvcmVhY2gtZWxzLmpzIiwibGliL2ZvcmVhY2gtc2VsZWN0b3JzLmpzIiwibGliL2lzLXN1cHBvcnRlZC5qcyIsImxpYi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQuanMiLCJsaWIvcHJvdG8vYXR0YWNoLWZvcm0uanMiLCJsaWIvcHJvdG8vYXR0YWNoLWxpbmsuanMiLCJsaWIvcHJvdG8vZ2V0LWVsZW1lbnRzLmpzIiwibGliL3Byb3RvL2xvZy5qcyIsImxpYi9wcm90by9wYXJzZS1kb20uanMiLCJsaWIvcHJvdG8vcGFyc2UtZWxlbWVudC5qcyIsImxpYi9wcm90by9wYXJzZS1vcHRpb25zLmpzIiwibGliL3Byb3RvL3JlZnJlc2guanMiLCJsaWIvcmVsb2FkLmpzIiwibGliL3JlcXVlc3QuanMiLCJsaWIvc3dpdGNoZXMtc2VsZWN0b3JzLmpzIiwibGliL3N3aXRjaGVzLmpzIiwibGliL3VuaXF1ZWlkLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMxUEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNqREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDL0JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzNCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNUQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pGQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNQQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3ZDQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuSEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsInZhciBjbG9uZSA9IHJlcXVpcmUoJy4vbGliL2Nsb25lLmpzJylcbnZhciBleGVjdXRlU2NyaXB0cyA9IHJlcXVpcmUoJy4vbGliL2V4ZWN1dGUtc2NyaXB0cy5qcycpXG52YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2xpYi9mb3JlYWNoLWVscy5qc1wiKVxuXG52YXIgbmV3VWlkID0gcmVxdWlyZShcIi4vbGliL3VuaXF1ZWlkLmpzXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvb24uanNcIilcbi8vIHZhciBvZmYgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL29uLmpzXCIpXG52YXIgdHJpZ2dlciA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvdHJpZ2dlci5qc1wiKVxuXG5cbnZhciBQamF4ID0gZnVuY3Rpb24ob3B0aW9ucykge1xuICAgIHRoaXMuZmlyc3RydW4gPSB0cnVlXG5cbiAgICB2YXIgcGFyc2VPcHRpb25zID0gcmVxdWlyZShcIi4vbGliL3Byb3RvL3BhcnNlLW9wdGlvbnMuanNcIik7XG4gICAgcGFyc2VPcHRpb25zLmFwcGx5KHRoaXMsW29wdGlvbnNdKVxuICAgIHRoaXMubG9nKFwiUGpheCBvcHRpb25zXCIsIHRoaXMub3B0aW9ucylcblxuICAgIHRoaXMubWF4VWlkID0gdGhpcy5sYXN0VWlkID0gbmV3VWlkKClcblxuICAgIHRoaXMucGFyc2VET00oZG9jdW1lbnQpXG5cbiAgICBvbih3aW5kb3csIFwicG9wc3RhdGVcIiwgZnVuY3Rpb24oc3QpIHtcbiAgICAgIGlmIChzdC5zdGF0ZSkge1xuICAgICAgICB2YXIgb3B0ID0gY2xvbmUodGhpcy5vcHRpb25zKVxuICAgICAgICBvcHQudXJsID0gc3Quc3RhdGUudXJsXG4gICAgICAgIG9wdC50aXRsZSA9IHN0LnN0YXRlLnRpdGxlXG4gICAgICAgIG9wdC5oaXN0b3J5ID0gZmFsc2VcbiAgICAgICAgb3B0LnJlcXVlc3RPcHRpb25zID0ge307XG4gICAgICAgIGlmIChzdC5zdGF0ZS51aWQgPCB0aGlzLmxhc3RVaWQpIHtcbiAgICAgICAgICBvcHQuYmFja3dhcmQgPSB0cnVlXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgb3B0LmZvcndhcmQgPSB0cnVlXG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5sYXN0VWlkID0gc3Quc3RhdGUudWlkXG5cbiAgICAgICAgLy8gQHRvZG8gaW1wbGVtZW50IGhpc3RvcnkgY2FjaGUgaGVyZSwgYmFzZWQgb24gdWlkXG4gICAgICAgIHRoaXMubG9hZFVybChzdC5zdGF0ZS51cmwsIG9wdClcbiAgICAgIH1cbiAgICB9LmJpbmQodGhpcykpXG4gIH1cblxuUGpheC5wcm90b3R5cGUgPSB7XG4gIGxvZzogcmVxdWlyZShcIi4vbGliL3Byb3RvL2xvZy5qc1wiKSxcblxuICBnZXRFbGVtZW50czogcmVxdWlyZShcIi4vbGliL3Byb3RvL2dldC1lbGVtZW50cy5qc1wiKSxcblxuICBwYXJzZURPTTogcmVxdWlyZShcIi4vbGliL3Byb3RvL3BhcnNlLWRvbS5qc1wiKSxcblxuICByZWZyZXNoOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vcmVmcmVzaC5qc1wiKSxcblxuICByZWxvYWQ6IHJlcXVpcmUoXCIuL2xpYi9yZWxvYWQuanNcIiksXG5cbiAgYXR0YWNoTGluazogcmVxdWlyZShcIi4vbGliL3Byb3RvL2F0dGFjaC1saW5rLmpzXCIpLFxuXG4gIGF0dGFjaEZvcm06IHJlcXVpcmUoXCIuL2xpYi9wcm90by9hdHRhY2gtZm9ybS5qc1wiKSxcblxuICBmb3JFYWNoU2VsZWN0b3JzOiBmdW5jdGlvbihjYiwgY29udGV4dCwgRE9NY29udGV4dCkge1xuICAgIHJldHVybiByZXF1aXJlKFwiLi9saWIvZm9yZWFjaC1zZWxlY3RvcnMuanNcIikuYmluZCh0aGlzKSh0aGlzLm9wdGlvbnMuc2VsZWN0b3JzLCBjYiwgY29udGV4dCwgRE9NY29udGV4dClcbiAgfSxcblxuICBzd2l0Y2hTZWxlY3RvcnM6IGZ1bmN0aW9uKHNlbGVjdG9ycywgZnJvbUVsLCB0b0VsLCBvcHRpb25zKSB7XG4gICAgcmV0dXJuIHJlcXVpcmUoXCIuL2xpYi9zd2l0Y2hlcy1zZWxlY3RvcnMuanNcIikuYmluZCh0aGlzKSh0aGlzLm9wdGlvbnMuc3dpdGNoZXMsIHRoaXMub3B0aW9ucy5zd2l0Y2hlc09wdGlvbnMsIHNlbGVjdG9ycywgZnJvbUVsLCB0b0VsLCBvcHRpb25zKVxuICB9LFxuXG4gIC8vIHRvbyBtdWNoIHByb2JsZW0gd2l0aCB0aGUgY29kZSBiZWxvd1xuICAvLyArIGl04oCZcyB0b28gZGFuZ2Vyb3VzXG4vLyAgIHN3aXRjaEZhbGxiYWNrOiBmdW5jdGlvbihmcm9tRWwsIHRvRWwpIHtcbi8vICAgICB0aGlzLnN3aXRjaFNlbGVjdG9ycyhbXCJoZWFkXCIsIFwiYm9keVwiXSwgZnJvbUVsLCB0b0VsKVxuLy8gICAgIC8vIGV4ZWN1dGUgc2NyaXB0IHdoZW4gRE9NIGlzIGxpa2UgaXQgc2hvdWxkIGJlXG4vLyAgICAgUGpheC5leGVjdXRlU2NyaXB0cyhkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiaGVhZFwiKSlcbi8vICAgICBQamF4LmV4ZWN1dGVTY3JpcHRzKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXCJib2R5XCIpKVxuLy8gICB9XG5cbiAgbGF0ZXN0Q2hhbmNlOiBmdW5jdGlvbihocmVmKSB7XG4gICAgd2luZG93LmxvY2F0aW9uID0gaHJlZlxuICB9LFxuXG4gIG9uU3dpdGNoOiBmdW5jdGlvbigpIHtcbiAgICB0cmlnZ2VyKHdpbmRvdywgXCJyZXNpemUgc2Nyb2xsXCIpXG4gIH0sXG5cbiAgbG9hZENvbnRlbnQ6IGZ1bmN0aW9uKGh0bWwsIG9wdGlvbnMpIHtcbiAgICB2YXIgdG1wRWwgPSBkb2N1bWVudC5pbXBsZW1lbnRhdGlvbi5jcmVhdGVIVE1MRG9jdW1lbnQoXCJwamF4XCIpXG5cbiAgICAvLyBwYXJzZSBIVE1MIGF0dHJpYnV0ZXMgdG8gY29weSB0aGVtXG4gICAgLy8gc2luY2Ugd2UgYXJlIGZvcmNlZCB0byB1c2UgZG9jdW1lbnRFbGVtZW50LmlubmVySFRNTCAob3V0ZXJIVE1MIGNhbid0IGJlIHVzZWQgZm9yIDxodG1sPilcbiAgICB2YXIgaHRtbFJlZ2V4ID0gLzxodG1sW14+XSs+L2dpXG4gICAgdmFyIGh0bWxBdHRyaWJzUmVnZXggPSAvXFxzP1thLXo6XSsoPzpcXD0oPzpcXCd8XFxcIilbXlxcJ1xcXCI+XSsoPzpcXCd8XFxcIikpKi9naVxuICAgIHZhciBtYXRjaGVzID0gaHRtbC5tYXRjaChodG1sUmVnZXgpXG4gICAgaWYgKG1hdGNoZXMgJiYgbWF0Y2hlcy5sZW5ndGgpIHtcbiAgICAgIG1hdGNoZXMgPSBtYXRjaGVzWzBdLm1hdGNoKGh0bWxBdHRyaWJzUmVnZXgpXG4gICAgICBpZiAobWF0Y2hlcy5sZW5ndGgpIHtcbiAgICAgICAgbWF0Y2hlcy5zaGlmdCgpXG4gICAgICAgIG1hdGNoZXMuZm9yRWFjaChmdW5jdGlvbihodG1sQXR0cmliKSB7XG4gICAgICAgICAgdmFyIGF0dHIgPSBodG1sQXR0cmliLnRyaW0oKS5zcGxpdChcIj1cIilcbiAgICAgICAgICBpZiAoYXR0ci5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICAgIHRtcEVsLmRvY3VtZW50RWxlbWVudC5zZXRBdHRyaWJ1dGUoYXR0clswXSwgdHJ1ZSlcbiAgICAgICAgICB9XG4gICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuc2V0QXR0cmlidXRlKGF0dHJbMF0sIGF0dHJbMV0uc2xpY2UoMSwgLTEpKVxuICAgICAgICAgIH1cbiAgICAgICAgfSlcbiAgICAgIH1cbiAgICB9XG5cbiAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuaW5uZXJIVE1MID0gaHRtbFxuICAgIHRoaXMubG9nKFwibG9hZCBjb250ZW50XCIsIHRtcEVsLmRvY3VtZW50RWxlbWVudC5hdHRyaWJ1dGVzLCB0bXBFbC5kb2N1bWVudEVsZW1lbnQuaW5uZXJIVE1MLmxlbmd0aClcblxuICAgIC8vIENsZWFyIG91dCBhbnkgZm9jdXNlZCBjb250cm9scyBiZWZvcmUgaW5zZXJ0aW5nIG5ldyBwYWdlIGNvbnRlbnRzLlxuICAgIC8vIHdlIGNsZWFyIGZvY3VzIG9uIG5vbiBmb3JtIGVsZW1lbnRzXG4gICAgaWYgKGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQgJiYgIWRvY3VtZW50LmFjdGl2ZUVsZW1lbnQudmFsdWUpIHtcbiAgICAgIHRyeSB7XG4gICAgICAgIGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQuYmx1cigpXG4gICAgICB9IGNhdGNoIChlKSB7IH1cbiAgICB9XG5cbiAgICAvLyB0cnkge1xuICAgIHRoaXMuc3dpdGNoU2VsZWN0b3JzKHRoaXMub3B0aW9ucy5zZWxlY3RvcnMsIHRtcEVsLCBkb2N1bWVudCwgb3B0aW9ucylcblxuICAgIC8vIEZGIGJ1ZzogV29u4oCZdCBhdXRvZm9jdXMgZmllbGRzIHRoYXQgYXJlIGluc2VydGVkIHZpYSBKUy5cbiAgICAvLyBUaGlzIGJlaGF2aW9yIGlzIGluY29ycmVjdC4gU28gaWYgdGhlcmVzIG5vIGN1cnJlbnQgZm9jdXMsIGF1dG9mb2N1c1xuICAgIC8vIHRoZSBsYXN0IGZpZWxkLlxuICAgIC8vXG4gICAgLy8gaHR0cDovL3d3dy53My5vcmcvaHRtbC93Zy9kcmFmdHMvaHRtbC9tYXN0ZXIvZm9ybXMuaHRtbFxuICAgIHZhciBhdXRvZm9jdXNFbCA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoXCJbYXV0b2ZvY3VzXVwiKSkucG9wKClcbiAgICBpZiAoYXV0b2ZvY3VzRWwgJiYgZG9jdW1lbnQuYWN0aXZlRWxlbWVudCAhPT0gYXV0b2ZvY3VzRWwpIHtcbiAgICAgIGF1dG9mb2N1c0VsLmZvY3VzKCk7XG4gICAgfVxuXG4gICAgLy8gZXhlY3V0ZSBzY3JpcHRzIHdoZW4gRE9NIGhhdmUgYmVlbiBjb21wbGV0ZWx5IHVwZGF0ZWRcbiAgICB0aGlzLm9wdGlvbnMuc2VsZWN0b3JzLmZvckVhY2goIGZ1bmN0aW9uKHNlbGVjdG9yKSB7XG4gICAgICB2YXIgY29sbGVjdEZvclNjcmlwdGNvbXBsZXRlID0gW107XG5cbiAgICAgIGZvckVhY2hFbHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvciksIGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgIGNvbGxlY3RGb3JTY3JpcHRjb21wbGV0ZS5wdXNoKGV4ZWN1dGVTY3JpcHRzLmNhbGwodGhpcywgZWwpKTtcbiAgICAgIH0sIHRoaXMpO1xuXG4gICAgICBQcm9taXNlLmFsbChjb2xsZWN0Rm9yU2NyaXB0Y29tcGxldGUpLnRoZW4oZnVuY3Rpb24oKXtcbiAgICAgICAgZG9jdW1lbnQuZGlzcGF0Y2hFdmVudCgobmV3IEV2ZW50KFwicGpheDpzY3JpcHRjb21wbGV0ZVwiKSkpO1xuICAgICAgfSk7XG5cbiAgICB9LHRoaXMpO1xuICAgIC8vIH1cbiAgICAvLyBjYXRjaChlKSB7XG4gICAgLy8gICBpZiAodGhpcy5vcHRpb25zLmRlYnVnKSB7XG4gICAgLy8gICAgIHRoaXMubG9nKFwiUGpheCBzd2l0Y2ggZmFpbDogXCIsIGUpXG4gICAgLy8gICB9XG4gICAgLy8gICB0aGlzLnN3aXRjaEZhbGxiYWNrKHRtcEVsLCBkb2N1bWVudClcbiAgICAvLyB9XG4gIH0sXG5cbiAgZG9SZXF1ZXN0OiByZXF1aXJlKFwiLi9saWIvcmVxdWVzdC5qc1wiKSxcblxuICBsb2FkVXJsOiBmdW5jdGlvbihocmVmLCBvcHRpb25zKSB7XG4gICAgdGhpcy5sb2coXCJsb2FkIGhyZWZcIiwgaHJlZiwgb3B0aW9ucylcblxuICAgIHRyaWdnZXIoZG9jdW1lbnQsIFwicGpheDpzZW5kXCIsIG9wdGlvbnMpO1xuXG4gICAgLy8gRG8gdGhlIHJlcXVlc3RcbiAgICB0aGlzLmRvUmVxdWVzdChocmVmLCBvcHRpb25zLnJlcXVlc3RPcHRpb25zLCBmdW5jdGlvbihodG1sKSB7XG4gICAgICAvLyBGYWlsIGlmIHVuYWJsZSB0byBsb2FkIEhUTUwgdmlhIEFKQVhcbiAgICAgIGlmIChodG1sID09PSBmYWxzZSkge1xuICAgICAgICB0cmlnZ2VyKGRvY3VtZW50LFwicGpheDpjb21wbGV0ZSBwamF4OmVycm9yXCIsIG9wdGlvbnMpXG5cbiAgICAgICAgcmV0dXJuXG4gICAgICB9XG5cbiAgICAgIC8vIENsZWFyIG91dCBhbnkgZm9jdXNlZCBjb250cm9scyBiZWZvcmUgaW5zZXJ0aW5nIG5ldyBwYWdlIGNvbnRlbnRzLlxuICAgICAgZG9jdW1lbnQuYWN0aXZlRWxlbWVudC5ibHVyKClcblxuICAgICAgdHJ5IHtcbiAgICAgICAgdGhpcy5sb2FkQ29udGVudChodG1sLCBvcHRpb25zKVxuICAgICAgfVxuICAgICAgY2F0Y2ggKGUpIHtcbiAgICAgICAgaWYgKCF0aGlzLm9wdGlvbnMuZGVidWcpIHtcbiAgICAgICAgICBpZiAoY29uc29sZSAmJiBjb25zb2xlLmVycm9yKSB7XG4gICAgICAgICAgICBjb25zb2xlLmVycm9yKFwiUGpheCBzd2l0Y2ggZmFpbDogXCIsIGUpXG4gICAgICAgICAgfVxuICAgICAgICAgIHRoaXMubGF0ZXN0Q2hhbmNlKGhyZWYpXG4gICAgICAgICAgcmV0dXJuXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgdGhyb3cgZVxuICAgICAgICB9XG4gICAgICB9XG5cbiAgICAgIGlmIChvcHRpb25zLmhpc3RvcnkpIHtcbiAgICAgICAgaWYgKHRoaXMuZmlyc3RydW4pIHtcbiAgICAgICAgICB0aGlzLmxhc3RVaWQgPSB0aGlzLm1heFVpZCA9IG5ld1VpZCgpXG4gICAgICAgICAgdGhpcy5maXJzdHJ1biA9IGZhbHNlXG4gICAgICAgICAgd2luZG93Lmhpc3RvcnkucmVwbGFjZVN0YXRlKHtcbiAgICAgICAgICAgIHVybDogd2luZG93LmxvY2F0aW9uLmhyZWYsXG4gICAgICAgICAgICB0aXRsZTogZG9jdW1lbnQudGl0bGUsXG4gICAgICAgICAgICB1aWQ6IHRoaXMubWF4VWlkXG4gICAgICAgICAgfSxcbiAgICAgICAgICBkb2N1bWVudC50aXRsZSlcbiAgICAgICAgfVxuXG4gICAgICAgIC8vIFVwZGF0ZSBicm93c2VyIGhpc3RvcnlcbiAgICAgICAgdGhpcy5sYXN0VWlkID0gdGhpcy5tYXhVaWQgPSBuZXdVaWQoKVxuICAgICAgICB3aW5kb3cuaGlzdG9yeS5wdXNoU3RhdGUoe1xuICAgICAgICAgIHVybDogaHJlZixcbiAgICAgICAgICB0aXRsZTogb3B0aW9ucy50aXRsZSxcbiAgICAgICAgICB1aWQ6IHRoaXMubWF4VWlkXG4gICAgICAgIH0sXG4gICAgICAgICAgb3B0aW9ucy50aXRsZSxcbiAgICAgICAgICBocmVmKVxuICAgICAgfVxuXG4gICAgICB0aGlzLmZvckVhY2hTZWxlY3RvcnMoZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgdGhpcy5wYXJzZURPTShlbClcbiAgICAgIH0sIHRoaXMpXG5cbiAgICAgIC8vIEZpcmUgRXZlbnRzXG4gICAgICB0cmlnZ2VyKGRvY3VtZW50LFwicGpheDpjb21wbGV0ZSBwamF4OnN1Y2Nlc3NcIiwgb3B0aW9ucylcblxuICAgICAgb3B0aW9ucy5hbmFseXRpY3MoKVxuXG4gICAgICAvLyBTY3JvbGwgcGFnZSB0byB0b3Agb24gbmV3IHBhZ2UgbG9hZFxuICAgICAgaWYgKG9wdGlvbnMuc2Nyb2xsVG8gIT09IGZhbHNlKSB7XG4gICAgICAgIGlmIChvcHRpb25zLnNjcm9sbFRvLmxlbmd0aCA+IDEpIHtcbiAgICAgICAgICB3aW5kb3cuc2Nyb2xsVG8ob3B0aW9ucy5zY3JvbGxUb1swXSwgb3B0aW9ucy5zY3JvbGxUb1sxXSlcbiAgICAgICAgfVxuICAgICAgICBlbHNlIHtcbiAgICAgICAgICB3aW5kb3cuc2Nyb2xsVG8oMCwgb3B0aW9ucy5zY3JvbGxUbylcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH0uYmluZCh0aGlzKSlcbiAgfVxufVxuXG5QamF4LmlzU3VwcG9ydGVkID0gcmVxdWlyZShcIi4vbGliL2lzLXN1cHBvcnRlZC5qc1wiKTtcblxuLy9hcmd1YWJseSBjb3VsZCBkbyBgaWYoIHJlcXVpcmUoXCIuL2xpYi9pcy1zdXBwb3J0ZWQuanNcIikoKSkge2AgYnV0IHRoYXQgbWlnaHQgYmUgYSBsaXR0bGUgdG8gc2ltcGxlXG5pZiAoUGpheC5pc1N1cHBvcnRlZCgpKSB7XG4gIG1vZHVsZS5leHBvcnRzID0gUGpheFxufVxuLy8gaWYgdGhlcmUgaXNu4oCZdCByZXF1aXJlZCBicm93c2VyIGZ1bmN0aW9ucywgcmV0dXJuaW5nIHN0dXBpZCBhcGlcbmVsc2Uge1xuICB2YXIgc3R1cGlkUGpheCA9IGZ1bmN0aW9uKCkge31cbiAgZm9yICh2YXIga2V5IGluIFBqYXgucHJvdG90eXBlKSB7XG4gICAgaWYgKFBqYXgucHJvdG90eXBlLmhhc093blByb3BlcnR5KGtleSkgJiYgdHlwZW9mIFBqYXgucHJvdG90eXBlW2tleV0gPT09IFwiZnVuY3Rpb25cIikge1xuICAgICAgc3R1cGlkUGpheFtrZXldID0gc3R1cGlkUGpheFxuICAgIH1cbiAgfVxuXG4gIG1vZHVsZS5leHBvcnRzID0gc3R1cGlkUGpheFxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihvYmopIHtcbiAgaWYgKG51bGwgPT09IG9iaiB8fCBcIm9iamVjdFwiICE9IHR5cGVvZiBvYmopIHtcbiAgICByZXR1cm4gb2JqXG4gIH1cbiAgdmFyIGNvcHkgPSBvYmouY29uc3RydWN0b3IoKVxuICBmb3IgKHZhciBhdHRyIGluIG9iaikge1xuICAgIGlmIChvYmouaGFzT3duUHJvcGVydHkoYXR0cikpIHtcbiAgICAgIGNvcHlbYXR0cl0gPSBvYmpbYXR0cl1cbiAgICB9XG4gIH1cbiAgcmV0dXJuIGNvcHlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgdmFyIHF1ZXJ5U2VsZWN0b3IgPSB0aGlzLm9wdGlvbnMubWFpblNjcmlwdEVsZW1lbnQ7XG4gIHZhciBjb2RlID0gKGVsLnRleHQgfHwgZWwudGV4dENvbnRlbnQgfHwgZWwuaW5uZXJIVE1MIHx8IFwiXCIpXG4gIHZhciBzcmMgPSAoZWwuc3JjIHx8IFwiXCIpO1xuICB2YXIgcGFyZW50ID0gZWwucGFyZW50Tm9kZSB8fCBkb2N1bWVudC5xdWVyeVNlbGVjdG9yKHF1ZXJ5U2VsZWN0b3IpIHx8IGRvY3VtZW50LmRvY3VtZW50RWxlbWVudFxuICB2YXIgc2NyaXB0ID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudChcInNjcmlwdFwiKVxuICB2YXIgcHJvbWlzZSA9IG51bGw7XG5cbiAgdGhpcy5sb2coXCJFdmFsdWF0aW5nIFNjcmlwdDogXCIsIGVsKTtcblxuICBpZiAoY29kZS5tYXRjaChcImRvY3VtZW50LndyaXRlXCIpKSB7XG4gICAgaWYgKGNvbnNvbGUgJiYgY29uc29sZS5sb2cpIHtcbiAgICAgIGNvbnNvbGUubG9nKFwiU2NyaXB0IGNvbnRhaW5zIGRvY3VtZW50LndyaXRlLiBDYW7igJl0IGJlIGV4ZWN1dGVkIGNvcnJlY3RseS4gQ29kZSBza2lwcGVkIFwiLCBlbClcbiAgICB9XG4gICAgcmV0dXJuIGZhbHNlXG4gIH1cblxuICBwcm9taXNlID0gbmV3IFByb21pc2UoZnVuY3Rpb24ocmVzb2x2ZSwgcmVqZWN0KXtcblxuICAgIHNjcmlwdC50eXBlID0gXCJ0ZXh0L2phdmFzY3JpcHRcIlxuICAgIGlmIChzcmMgIT0gXCJcIikge1xuICAgICAgc2NyaXB0LnNyYyA9IHNyYztcbiAgICAgIHNjcmlwdC5vbmxvYWQgPSByZXNvbHZlO1xuICAgICAgc2NyaXB0LmFzeW5jID0gZmFsc2U7IC8vIGZvcmNlIGFzeW5jaHJvbm91cyBsb2FkaW5nIG9mIHBlcmlwaGVyYWwganNcbiAgICB9XG5cbiAgICBpZiAoY29kZSAhPSBcIlwiKSB7XG4gICAgICB0cnkge1xuICAgICAgICBzY3JpcHQuYXBwZW5kQ2hpbGQoZG9jdW1lbnQuY3JlYXRlVGV4dE5vZGUoY29kZSkpXG4gICAgICB9XG4gICAgICBjYXRjaCAoZSkge1xuICAgICAgICAvLyBvbGQgSUVzIGhhdmUgZnVua3kgc2NyaXB0IG5vZGVzXG4gICAgICAgIHNjcmlwdC50ZXh0ID0gY29kZVxuICAgICAgfVxuICAgICAgcmVzb2x2ZSgpO1xuICAgIH1cbiAgfSk7XG5cbiAgdGhpcy5sb2coJ1BhcmVudEVsZW1lbnQgPT4gJywgcGFyZW50ICk7XG5cbiAgLy8gZXhlY3V0ZVxuICBwYXJlbnQuYXBwZW5kQ2hpbGQoc2NyaXB0KTtcbiAgLy8gYXZvaWQgcG9sbHV0aW9uIG9ubHkgaW4gaGVhZCBvciBib2R5IHRhZ3NcbiAgaWYgKFtcImhlYWRcIixcImJvZHlcIl0uaW5kZXhPZiggcGFyZW50LnRhZ05hbWUudG9Mb3dlckNhc2UoKSkgPiAwKSB7XG4gICAgcGFyZW50LnJlbW92ZUNoaWxkKHNjcmlwdClcbiAgfVxuXG4gIHJldHVybiBwcm9taXNlO1xufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbHMsIGV2ZW50cywgbGlzdGVuZXIsIHVzZUNhcHR1cmUpIHtcbiAgZXZlbnRzID0gKHR5cGVvZiBldmVudHMgPT09IFwic3RyaW5nXCIgPyBldmVudHMuc3BsaXQoXCIgXCIpIDogZXZlbnRzKVxuXG4gIGV2ZW50cy5mb3JFYWNoKGZ1bmN0aW9uKGUpIHtcbiAgICBmb3JFYWNoRWxzKGVscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIGVsLmFkZEV2ZW50TGlzdGVuZXIoZSwgbGlzdGVuZXIsIHVzZUNhcHR1cmUpXG4gICAgfSlcbiAgfSlcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4uL2ZvcmVhY2gtZWxzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxzLCBldmVudHMsIG9wdHMpIHtcbiAgZXZlbnRzID0gKHR5cGVvZiBldmVudHMgPT09IFwic3RyaW5nXCIgPyBldmVudHMuc3BsaXQoXCIgXCIpIDogZXZlbnRzKVxuXG4gIGV2ZW50cy5mb3JFYWNoKGZ1bmN0aW9uKGUpIHtcbiAgICB2YXIgZXZlbnQgLy8gPSBuZXcgQ3VzdG9tRXZlbnQoZSkgLy8gZG9lc24ndCBldmVyeXdoZXJlIHlldFxuICAgIGV2ZW50ID0gZG9jdW1lbnQuY3JlYXRlRXZlbnQoXCJIVE1MRXZlbnRzXCIpXG4gICAgZXZlbnQuaW5pdEV2ZW50KGUsIHRydWUsIHRydWUpXG4gICAgZXZlbnQuZXZlbnROYW1lID0gZVxuICAgIGlmIChvcHRzKSB7XG4gICAgICBPYmplY3Qua2V5cyhvcHRzKS5mb3JFYWNoKGZ1bmN0aW9uKGtleSkge1xuICAgICAgICBldmVudFtrZXldID0gb3B0c1trZXldXG4gICAgICB9KVxuICAgIH1cblxuICAgIGZvckVhY2hFbHMoZWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgdmFyIGRvbUZpeCA9IGZhbHNlXG4gICAgICBpZiAoIWVsLnBhcmVudE5vZGUgJiYgZWwgIT09IGRvY3VtZW50ICYmIGVsICE9PSB3aW5kb3cpIHtcbiAgICAgICAgLy8gVEhBTktTIFlPVSBJRSAoOS8xMC8vMTEgY29uY2VybmVkKVxuICAgICAgICAvLyBkaXNwYXRjaEV2ZW50IGRvZXNuJ3Qgd29yayBpZiBlbGVtZW50IGlzIG5vdCBpbiB0aGUgZG9tXG4gICAgICAgIGRvbUZpeCA9IHRydWVcbiAgICAgICAgZG9jdW1lbnQuYm9keS5hcHBlbmRDaGlsZChlbClcbiAgICAgIH1cbiAgICAgIGVsLmRpc3BhdGNoRXZlbnQoZXZlbnQpXG4gICAgICBpZiAoZG9tRml4KSB7XG4gICAgICAgIGVsLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoZWwpXG4gICAgICB9XG4gICAgfSlcbiAgfSlcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcbnZhciBldmFsU2NyaXB0ID0gcmVxdWlyZShcIi4vZXZhbC1zY3JpcHRcIilcbi8vIEZpbmRzIGFuZCBleGVjdXRlcyBzY3JpcHRzICh1c2VkIGZvciBuZXdseSBhZGRlZCBlbGVtZW50cylcbi8vIE5lZWRlZCBzaW5jZSBpbm5lckhUTUwgZG9lcyBub3QgcnVuIHNjcmlwdHNcbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcblxuICB0aGlzLmxvZyhcIkV4ZWN1dGluZyBzY3JpcHRzIGZvciBcIiwgZWwpO1xuXG4gIHZhciBsb2FkaW5nU2NyaXB0cyA9IFtdO1xuXG4gIGlmKGVsID09PSB1bmRlZmluZWQpIHJldHVybiBQcm9taXNlLnJlc29sdmUoKTtcblxuICBpZiAoZWwudGFnTmFtZS50b0xvd2VyQ2FzZSgpID09PSBcInNjcmlwdFwiKSB7XG4gICAgZXZhbFNjcmlwdC5jYWxsKHRoaXMsIGVsKTtcbiAgfVxuXG4gIGZvckVhY2hFbHMoZWwucXVlcnlTZWxlY3RvckFsbChcInNjcmlwdFwiKSwgZnVuY3Rpb24oc2NyaXB0KSB7XG4gICAgaWYgKCFzY3JpcHQudHlwZSB8fCBzY3JpcHQudHlwZS50b0xvd2VyQ2FzZSgpID09PSBcInRleHQvamF2YXNjcmlwdFwiKSB7XG4gICAgICAvLyBpZiAoc2NyaXB0LnBhcmVudE5vZGUpIHtcbiAgICAgIC8vICAgc2NyaXB0LnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoc2NyaXB0KVxuICAgICAgLy8gfVxuICAgICAgbG9hZGluZ1NjcmlwdHMucHVzaChldmFsU2NyaXB0LmNhbGwodGhpcywgc2NyaXB0KSk7XG4gICAgfVxuICB9LCB0aGlzKTtcblxuICByZXR1cm4gUHJvbWlzZS5hbGwobG9hZGluZ1NjcmlwdHMpO1xufVxuIiwiLyogZ2xvYmFsIEhUTUxDb2xsZWN0aW9uOiB0cnVlICovXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxzLCBmbiwgY29udGV4dCkge1xuICBpZiAoZWxzIGluc3RhbmNlb2YgSFRNTENvbGxlY3Rpb24gfHwgZWxzIGluc3RhbmNlb2YgTm9kZUxpc3QgfHwgZWxzIGluc3RhbmNlb2YgQXJyYXkpIHtcbiAgICByZXR1cm4gQXJyYXkucHJvdG90eXBlLmZvckVhY2guY2FsbChlbHMsIGZuLCBjb250ZXh0KVxuICB9XG4gIC8vIGFzc3VtZSBzaW1wbGUgZG9tIGVsZW1lbnRcbiAgcmV0dXJuIGZuLmNhbGwoY29udGV4dCwgZWxzKVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9mb3JlYWNoLWVsc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKHNlbGVjdG9ycywgY2IsIGNvbnRleHQsIERPTWNvbnRleHQpIHtcbiAgRE9NY29udGV4dCA9IERPTWNvbnRleHQgfHwgZG9jdW1lbnRcbiAgc2VsZWN0b3JzLmZvckVhY2goZnVuY3Rpb24oc2VsZWN0b3IpIHtcbiAgICBmb3JFYWNoRWxzKERPTWNvbnRleHQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvciksIGNiLCBjb250ZXh0KVxuICB9KVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbigpIHtcbiAgLy8gQm9ycm93ZWQgd2hvbGVzYWxlIGZyb20gaHR0cHM6Ly9naXRodWIuY29tL2RlZnVua3QvanF1ZXJ5LXBqYXhcbiAgcmV0dXJuIHdpbmRvdy5oaXN0b3J5ICYmXG4gICAgd2luZG93Lmhpc3RvcnkucHVzaFN0YXRlICYmXG4gICAgd2luZG93Lmhpc3RvcnkucmVwbGFjZVN0YXRlICYmXG4gICAgLy8gcHVzaFN0YXRlIGlzbuKAmXQgcmVsaWFibGUgb24gaU9TIHVudGlsIDUuXG4gICAgIW5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goLygoaVBvZHxpUGhvbmV8aVBhZCkuK1xcYk9TXFxzK1sxLTRdXFxEfFdlYkFwcHNcXC8uK0NGTmV0d29yaykvKVxufVxuIiwiaWYgKCFGdW5jdGlvbi5wcm90b3R5cGUuYmluZCkge1xuICBGdW5jdGlvbi5wcm90b3R5cGUuYmluZCA9IGZ1bmN0aW9uKG9UaGlzKSB7XG4gICAgaWYgKHR5cGVvZiB0aGlzICE9PSBcImZ1bmN0aW9uXCIpIHtcbiAgICAgIC8vIGNsb3Nlc3QgdGhpbmcgcG9zc2libGUgdG8gdGhlIEVDTUFTY3JpcHQgNSBpbnRlcm5hbCBJc0NhbGxhYmxlIGZ1bmN0aW9uXG4gICAgICB0aHJvdyBuZXcgVHlwZUVycm9yKFwiRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQgLSB3aGF0IGlzIHRyeWluZyB0byBiZSBib3VuZCBpcyBub3QgY2FsbGFibGVcIilcbiAgICB9XG5cbiAgICB2YXIgYUFyZ3MgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMsIDEpXG4gICAgdmFyIHRoYXQgPSB0aGlzXG4gICAgdmFyIEZub29wID0gZnVuY3Rpb24oKSB7fVxuICAgIHZhciBmQm91bmQgPSBmdW5jdGlvbigpIHtcbiAgICAgIHJldHVybiB0aGF0LmFwcGx5KHRoaXMgaW5zdGFuY2VvZiBGbm9vcCAmJiBvVGhpcyA/IHRoaXMgOiBvVGhpcywgYUFyZ3MuY29uY2F0KEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cykpKVxuICAgIH1cblxuICAgIEZub29wLnByb3RvdHlwZSA9IHRoaXMucHJvdG90eXBlXG4gICAgZkJvdW5kLnByb3RvdHlwZSA9IG5ldyBGbm9vcCgpXG5cbiAgICByZXR1cm4gZkJvdW5kXG4gIH1cbn1cbiIsInJlcXVpcmUoXCIuLi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmRcIilcblxudmFyIG9uID0gcmVxdWlyZShcIi4uL2V2ZW50cy9vblwiKVxudmFyIGNsb25lID0gcmVxdWlyZShcIi4uL2Nsb25lXCIpXG5cbnZhciBhdHRyQ2xpY2sgPSBcImRhdGEtcGpheC1jbGljay1zdGF0ZVwiXG5cbnZhciBmb3JtQWN0aW9uID0gZnVuY3Rpb24oZWwsIGV2ZW50KXtcblxuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgPSB7XG4gICAgcmVxdWVzdFVybCA6IGVsLmdldEF0dHJpYnV0ZSgnYWN0aW9uJykgfHwgd2luZG93LmxvY2F0aW9uLmhyZWYsXG4gICAgcmVxdWVzdE1ldGhvZCA6IGVsLmdldEF0dHJpYnV0ZSgnbWV0aG9kJykgfHwgJ0dFVCcsXG4gIH1cblxuICAvL2NyZWF0ZSBhIHRlc3RhYmxlIHZpcnR1YWwgbGluayBvZiB0aGUgZm9ybSBhY3Rpb25cbiAgdmFyIHZpcnRMaW5rRWxlbWVudCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2EnKTtcbiAgdmlydExpbmtFbGVtZW50LnNldEF0dHJpYnV0ZSgnaHJlZicsIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0VXJsKTtcblxuICAvLyBJZ25vcmUgZXh0ZXJuYWwgbGlua3MuXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQucHJvdG9jb2wgIT09IHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCB8fCB2aXJ0TGlua0VsZW1lbnQuaG9zdCAhPT0gd2luZG93LmxvY2F0aW9uLmhvc3QpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImV4dGVybmFsXCIpO1xuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGNsaWNrIGlmIHdlIGFyZSBvbiBhbiBhbmNob3Igb24gdGhlIHNhbWUgcGFnZVxuICBpZiAodmlydExpbmtFbGVtZW50LnBhdGhuYW1lID09PSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgJiYgdmlydExpbmtFbGVtZW50Lmhhc2gubGVuZ3RoID4gMCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLXByZXNlbnRcIik7XG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgZW1wdHkgYW5jaG9yIFwiZm9vLmh0bWwjXCJcbiAgaWYgKHZpcnRMaW5rRWxlbWVudC5ocmVmID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5zcGxpdChcIiNcIilbMF0gKyBcIiNcIikge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLWVtcHR5XCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBpZiBkZWNsYXJlZCBhcyBhIGZ1bGwgcmVsb2FkLCBqdXN0IG5vcm1hbGx5IHN1Ym1pdCB0aGUgZm9ybVxuICBpZiAoIHRoaXMub3B0aW9ucy5jdXJyZW50VXJsRnVsbFJlbG9hZCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwicmVsb2FkXCIpO1xuICAgIHJldHVybjtcbiAgfVxuXG4gIGV2ZW50LnByZXZlbnREZWZhdWx0KClcbiAgdmFyIG5hbWVMaXN0ID0gW107XG4gIHZhciBwYXJhbU9iamVjdCA9IFtdO1xuICBmb3IodmFyIGVsZW1lbnRLZXkgaW4gZWwuZWxlbWVudHMpIHtcbiAgICB2YXIgZWxlbWVudCA9IGVsLmVsZW1lbnRzW2VsZW1lbnRLZXldO1xuICAgIGlmICghIWVsZW1lbnQubmFtZSAmJiBlbGVtZW50LmF0dHJpYnV0ZXMgIT09IHVuZGVmaW5lZCAmJiBlbGVtZW50LnRhZ05hbWUudG9Mb3dlckNhc2UoKSAhPT0gJ2J1dHRvbicpe1xuICAgICAgaWYgKFxuICAgICAgICAoZWxlbWVudC50eXBlICE9PSAnY2hlY2tib3gnICYmIGVsZW1lbnQudHlwZSAhPT0gJ3JhZGlvJykgfHwgZWxlbWVudC5jaGVja2VkXG4gICAgICApIHtcbiAgICAgICAgaWYobmFtZUxpc3QuaW5kZXhPZihlbGVtZW50Lm5hbWUpID09PSAtMSl7XG4gICAgICAgICAgbmFtZUxpc3QucHVzaChlbGVtZW50Lm5hbWUpO1xuICAgICAgICAgIHBhcmFtT2JqZWN0LnB1c2goeyBuYW1lOiBlbmNvZGVVUklDb21wb25lbnQoZWxlbWVudC5uYW1lKSwgdmFsdWU6IGVuY29kZVVSSUNvbXBvbmVudChlbGVtZW50LnZhbHVlKX0pO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuICB9XG5cblxuXG4gIC8vQ3JlYXRpbmcgYSBnZXRTdHJpbmdcbiAgdmFyIHBhcmFtc1N0cmluZyA9IChwYXJhbU9iamVjdC5tYXAoZnVuY3Rpb24odmFsdWUpe3JldHVybiB2YWx1ZS5uYW1lK1wiPVwiK3ZhbHVlLnZhbHVlO30pKS5qb2luKCcmJyk7XG5cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zLnJlcXVlc3RQYXlsb2FkID0gcGFyYW1PYmplY3Q7XG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyA9IHBhcmFtc1N0cmluZztcblxuICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInN1Ym1pdFwiKTtcblxuICB0aGlzLmxvYWRVcmwodmlydExpbmtFbGVtZW50LmhyZWYsIGNsb25lKHRoaXMub3B0aW9ucykpXG5cbn07XG5cbnZhciBpc0RlZmF1bHRQcmV2ZW50ZWQgPSBmdW5jdGlvbihldmVudCkge1xuICByZXR1cm4gZXZlbnQuZGVmYXVsdFByZXZlbnRlZCB8fCBldmVudC5yZXR1cm5WYWx1ZSA9PT0gZmFsc2U7XG59O1xuXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgdmFyIHRoYXQgPSB0aGlzXG5cbiAgb24oZWwsIFwic3VibWl0XCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIGZvcm1BY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gIH0pXG5cbiAgb24oZWwsIFwia2V5dXBcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG5cbiAgICBpZiAoZXZlbnQua2V5Q29kZSA9PSAxMykge1xuICAgICAgZm9ybUFjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgICB9XG4gIH0uYmluZCh0aGlzKSlcbn1cbiIsInJlcXVpcmUoXCIuLi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmRcIilcblxudmFyIG9uID0gcmVxdWlyZShcIi4uL2V2ZW50cy9vblwiKVxudmFyIGNsb25lID0gcmVxdWlyZShcIi4uL2Nsb25lXCIpXG5cbnZhciBhdHRyQ2xpY2sgPSBcImRhdGEtcGpheC1jbGljay1zdGF0ZVwiXG52YXIgYXR0cktleSA9IFwiZGF0YS1wamF4LWtleXVwLXN0YXRlXCJcblxudmFyIGxpbmtBY3Rpb24gPSBmdW5jdGlvbihlbCwgZXZlbnQpIHtcbiAgLy8gRG9u4oCZdCBicmVhayBicm93c2VyIHNwZWNpYWwgYmVoYXZpb3Igb24gbGlua3MgKGxpa2UgcGFnZSBpbiBuZXcgd2luZG93KVxuICBpZiAoZXZlbnQud2hpY2ggPiAxIHx8IGV2ZW50Lm1ldGFLZXkgfHwgZXZlbnQuY3RybEtleSB8fCBldmVudC5zaGlmdEtleSB8fCBldmVudC5hbHRLZXkpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcIm1vZGlmaWVyXCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyB3ZSBkbyB0ZXN0IG9uIGhyZWYgbm93IHRvIHByZXZlbnQgdW5leHBlY3RlZCBiZWhhdmlvciBpZiBmb3Igc29tZSByZWFzb25cbiAgLy8gdXNlciBoYXZlIGhyZWYgdGhhdCBjYW4gYmUgZHluYW1pY2FsbHkgdXBkYXRlZFxuXG4gIC8vIElnbm9yZSBleHRlcm5hbCBsaW5rcy5cbiAgaWYgKGVsLnByb3RvY29sICE9PSB3aW5kb3cubG9jYXRpb24ucHJvdG9jb2wgfHwgZWwuaG9zdCAhPT0gd2luZG93LmxvY2F0aW9uLmhvc3QpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImV4dGVybmFsXCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgY2xpY2sgaWYgd2UgYXJlIG9uIGFuIGFuY2hvciBvbiB0aGUgc2FtZSBwYWdlXG4gIGlmIChlbC5wYXRobmFtZSA9PT0gd2luZG93LmxvY2F0aW9uLnBhdGhuYW1lICYmIGVsLmhhc2gubGVuZ3RoID4gMCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLXByZXNlbnRcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBhbmNob3JzIG9uIHRoZSBzYW1lIHBhZ2UgKGtlZXAgbmF0aXZlIGJlaGF2aW9yKVxuICBpZiAoZWwuaGFzaCAmJiBlbC5ocmVmLnJlcGxhY2UoZWwuaGFzaCwgXCJcIikgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnJlcGxhY2UobG9jYXRpb24uaGFzaCwgXCJcIikpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvclwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGVtcHR5IGFuY2hvciBcImZvby5odG1sI1wiXG4gIGlmIChlbC5ocmVmID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5zcGxpdChcIiNcIilbMF0gKyBcIiNcIikge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLWVtcHR5XCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICBldmVudC5wcmV2ZW50RGVmYXVsdCgpXG5cbiAgLy8gZG9u4oCZdCBkbyBcIm5vdGhpbmdcIiBpZiB1c2VyIHRyeSB0byByZWxvYWQgdGhlIHBhZ2UgYnkgY2xpY2tpbmcgdGhlIHNhbWUgbGluayB0d2ljZVxuICBpZiAoXG4gICAgdGhpcy5vcHRpb25zLmN1cnJlbnRVcmxGdWxsUmVsb2FkICYmXG4gICAgZWwuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdXG4gICkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwicmVsb2FkXCIpXG4gICAgdGhpcy5yZWxvYWQoKVxuICAgIHJldHVyblxuICB9XG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucyA9IHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucyB8fCB7fTtcbiAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJsb2FkXCIpXG4gIHRoaXMubG9hZFVybChlbC5ocmVmLCBjbG9uZSh0aGlzLm9wdGlvbnMpKVxufVxuXG52YXIgaXNEZWZhdWx0UHJldmVudGVkID0gZnVuY3Rpb24oZXZlbnQpIHtcbiAgcmV0dXJuIGV2ZW50LmRlZmF1bHRQcmV2ZW50ZWQgfHwgZXZlbnQucmV0dXJuVmFsdWUgPT09IGZhbHNlO1xufVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciB0aGF0ID0gdGhpc1xuXG4gIG9uKGVsLCBcImNsaWNrXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIGxpbmtBY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gIH0pXG5cbiAgb24oZWwsIFwia2V5dXBcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgLy8gRG9u4oCZdCBicmVhayBicm93c2VyIHNwZWNpYWwgYmVoYXZpb3Igb24gbGlua3MgKGxpa2UgcGFnZSBpbiBuZXcgd2luZG93KVxuICAgIGlmIChldmVudC53aGljaCA+IDEgfHwgZXZlbnQubWV0YUtleSB8fCBldmVudC5jdHJsS2V5IHx8IGV2ZW50LnNoaWZ0S2V5IHx8IGV2ZW50LmFsdEtleSkge1xuICAgICAgZWwuc2V0QXR0cmlidXRlKGF0dHJLZXksIFwibW9kaWZpZXJcIilcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIGlmIChldmVudC5rZXlDb2RlID09IDEzKSB7XG4gICAgICBsaW5rQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICAgIH1cbiAgfS5iaW5kKHRoaXMpKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICByZXR1cm4gZWwucXVlcnlTZWxlY3RvckFsbCh0aGlzLm9wdGlvbnMuZWxlbWVudHMpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKCkge1xuICBpZiAoKHRoaXMub3B0aW9ucy5kZWJ1ZyAmJiBjb25zb2xlKSB8fCAxPT09MSkge1xuICAgIGlmICh0eXBlb2YgY29uc29sZS5sb2cgPT09IFwiZnVuY3Rpb25cIikge1xuICAgICAgY29uc29sZS5sb2cuYXBwbHkoY29uc29sZSwgYXJndW1lbnRzKTtcbiAgICB9XG4gICAgLy8gaWUgaXMgd2VpcmRcbiAgICBlbHNlIGlmIChjb25zb2xlLmxvZykge1xuICAgICAgY29uc29sZS5sb2coYXJndW1lbnRzKTtcbiAgICB9XG4gIH1cbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4uL2ZvcmVhY2gtZWxzXCIpXG5cbnZhciBwYXJzZUVsZW1lbnQgPSByZXF1aXJlKFwiLi9wYXJzZS1lbGVtZW50XCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgZm9yRWFjaEVscyh0aGlzLmdldEVsZW1lbnRzKGVsKSwgcGFyc2VFbGVtZW50LCB0aGlzKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICBzd2l0Y2ggKGVsLnRhZ05hbWUudG9Mb3dlckNhc2UoKSkge1xuICBjYXNlIFwiYVwiOlxuICAgIC8vIG9ubHkgYXR0YWNoIGxpbmsgaWYgZWwgZG9lcyBub3QgYWxyZWFkeSBoYXZlIGxpbmsgYXR0YWNoZWRcbiAgICBpZiAoIWVsLmhhc0F0dHJpYnV0ZSgnZGF0YS1wamF4LWNsaWNrLXN0YXRlJykpIHtcbiAgICAgIHRoaXMuYXR0YWNoTGluayhlbClcbiAgICB9XG4gICAgYnJlYWtcblxuICAgIGNhc2UgXCJmb3JtXCI6XG4gICAgICAvLyBvbmx5IGF0dGFjaCBsaW5rIGlmIGVsIGRvZXMgbm90IGFscmVhZHkgaGF2ZSBsaW5rIGF0dGFjaGVkXG4gICAgICBpZiAoIWVsLmhhc0F0dHJpYnV0ZSgnZGF0YS1wamF4LWNsaWNrLXN0YXRlJykpIHtcbiAgICAgICAgdGhpcy5hdHRhY2hGb3JtKGVsKVxuICAgICAgfVxuICAgIGJyZWFrXG5cbiAgZGVmYXVsdDpcbiAgICB0aHJvdyBcIlBqYXggY2FuIG9ubHkgYmUgYXBwbGllZCBvbiA8YT4gb3IgPGZvcm0+IHN1Ym1pdFwiXG4gIH1cbn1cbiIsIi8qIGdsb2JhbCBfZ2FxOiB0cnVlLCBnYTogdHJ1ZSAqL1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKG9wdGlvbnMpe1xuICB0aGlzLm9wdGlvbnMgPSBvcHRpb25zXG4gIHRoaXMub3B0aW9ucy5lbGVtZW50cyA9IHRoaXMub3B0aW9ucy5lbGVtZW50cyB8fCBcImFbaHJlZl0sIGZvcm1bYWN0aW9uXVwiXG4gIHRoaXMub3B0aW9ucy5tYWluU2NyaXB0RWxlbWVudCA9IHRoaXMub3B0aW9ucy5tYWluU2NyaXB0RWxlbWVudCB8fCBcImhlYWRcIlxuICB0aGlzLm9wdGlvbnMuc2VsZWN0b3JzID0gdGhpcy5vcHRpb25zLnNlbGVjdG9ycyB8fCBbXCJ0aXRsZVwiLCBcIi5qcy1QamF4XCJdXG4gIHRoaXMub3B0aW9ucy5zd2l0Y2hlcyA9IHRoaXMub3B0aW9ucy5zd2l0Y2hlcyB8fCB7fVxuICB0aGlzLm9wdGlvbnMuc3dpdGNoZXNPcHRpb25zID0gdGhpcy5vcHRpb25zLnN3aXRjaGVzT3B0aW9ucyB8fCB7fVxuICB0aGlzLm9wdGlvbnMuaGlzdG9yeSA9IHRoaXMub3B0aW9ucy5oaXN0b3J5IHx8IHRydWVcbiAgdGhpcy5vcHRpb25zLmFuYWx5dGljcyA9IHRoaXMub3B0aW9ucy5hbmFseXRpY3MgfHwgZnVuY3Rpb24oKSB7XG4gICAgLy8gb3B0aW9ucy5iYWNrd2FyZCBvciBvcHRpb25zLmZvd2FyZCBjYW4gYmUgdHJ1ZSBvciB1bmRlZmluZWRcbiAgICAvLyBieSBkZWZhdWx0LCB3ZSBkbyB0cmFjayBiYWNrL2Zvd2FyZCBoaXRcbiAgICAvLyBodHRwczovL3Byb2R1Y3Rmb3J1bXMuZ29vZ2xlLmNvbS9mb3J1bS8jIXRvcGljL2FuYWx5dGljcy9XVndNRGpMaFhZa1xuICAgIGlmICh3aW5kb3cuX2dhcSkge1xuICAgICAgX2dhcS5wdXNoKFtcIl90cmFja1BhZ2V2aWV3XCJdKVxuICAgIH1cbiAgICBpZiAod2luZG93LmdhKSB7XG4gICAgICBnYShcInNlbmRcIiwgXCJwYWdldmlld1wiLCB7cGFnZTogbG9jYXRpb24ucGF0aG5hbWUsIHRpdGxlOiBkb2N1bWVudC50aXRsZX0pXG4gICAgfVxuICB9XG4gIHRoaXMub3B0aW9ucy5zY3JvbGxUbyA9ICh0eXBlb2YgdGhpcy5vcHRpb25zLnNjcm9sbFRvID09PSAndW5kZWZpbmVkJykgPyAwIDogdGhpcy5vcHRpb25zLnNjcm9sbFRvO1xuICB0aGlzLm9wdGlvbnMuY2FjaGVCdXN0ID0gKHR5cGVvZiB0aGlzLm9wdGlvbnMuY2FjaGVCdXN0ID09PSAndW5kZWZpbmVkJykgPyB0cnVlIDogdGhpcy5vcHRpb25zLmNhY2hlQnVzdFxuICB0aGlzLm9wdGlvbnMuZGVidWcgPSB0aGlzLm9wdGlvbnMuZGVidWcgfHwgZmFsc2VcblxuICAvLyB3ZSBjYW7igJl0IHJlcGxhY2UgYm9keS5vdXRlckhUTUwgb3IgaGVhZC5vdXRlckhUTUxcbiAgLy8gaXQgY3JlYXRlIGEgYnVnIHdoZXJlIG5ldyBib2R5IG9yIG5ldyBoZWFkIGFyZSBjcmVhdGVkIGluIHRoZSBkb21cbiAgLy8gaWYgeW91IHNldCBoZWFkLm91dGVySFRNTCwgYSBuZXcgYm9keSB0YWcgaXMgYXBwZW5kZWQsIHNvIHRoZSBkb20gZ2V0IDIgYm9keVxuICAvLyAmIGl0IGJyZWFrIHRoZSBzd2l0Y2hGYWxsYmFjayB3aGljaCByZXBsYWNlIGhlYWQgJiBib2R5XG4gIGlmICghdGhpcy5vcHRpb25zLnN3aXRjaGVzLmhlYWQpIHtcbiAgICB0aGlzLm9wdGlvbnMuc3dpdGNoZXMuaGVhZCA9IHRoaXMuc3dpdGNoRWxlbWVudHNBbHRcbiAgfVxuICBpZiAoIXRoaXMub3B0aW9ucy5zd2l0Y2hlcy5ib2R5KSB7XG4gICAgdGhpcy5vcHRpb25zLnN3aXRjaGVzLmJvZHkgPSB0aGlzLnN3aXRjaEVsZW1lbnRzQWx0XG4gIH1cbiAgaWYgKHR5cGVvZiBvcHRpb25zLmFuYWx5dGljcyAhPT0gXCJmdW5jdGlvblwiKSB7XG4gICAgb3B0aW9ucy5hbmFseXRpY3MgPSBmdW5jdGlvbigpIHt9XG4gIH1cbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgdGhpcy5wYXJzZURPTShlbCB8fCBkb2N1bWVudClcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oKSB7XG4gIHdpbmRvdy5sb2NhdGlvbi5yZWxvYWQoKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihsb2NhdGlvbiwgb3B0aW9ucywgY2FsbGJhY2spIHtcbiAgb3B0aW9ucyA9IG9wdGlvbnMgfHwge307XG4gIHZhciByZXF1ZXN0TWV0aG9kID0gb3B0aW9ucy5yZXF1ZXN0TWV0aG9kIHx8IFwiR0VUXCI7XG4gIHZhciByZXF1ZXN0UGF5bG9hZCA9IG9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgfHwgbnVsbDtcbiAgdmFyIHJlcXVlc3QgPSBuZXcgWE1MSHR0cFJlcXVlc3QoKVxuXG4gIHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gNCkge1xuICAgICAgaWYgKHJlcXVlc3Quc3RhdHVzID09PSAyMDApIHtcbiAgICAgICAgY2FsbGJhY2socmVxdWVzdC5yZXNwb25zZVRleHQsIHJlcXVlc3QpXG4gICAgICB9XG4gICAgICBlbHNlIHtcbiAgICAgICAgY2FsbGJhY2sobnVsbCwgcmVxdWVzdClcbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICAvLyBBZGQgYSB0aW1lc3RhbXAgYXMgcGFydCBvZiB0aGUgcXVlcnkgc3RyaW5nIGlmIGNhY2hlIGJ1c3RpbmcgaXMgZW5hYmxlZFxuICBpZiAodGhpcy5vcHRpb25zLmNhY2hlQnVzdCkge1xuICAgIGxvY2F0aW9uICs9ICghL1s/Jl0vLnRlc3QobG9jYXRpb24pID8gXCI/XCIgOiBcIiZcIikgKyBuZXcgRGF0ZSgpLmdldFRpbWUoKVxuICB9XG5cbiAgcmVxdWVzdC5vcGVuKHJlcXVlc3RNZXRob2QudG9VcHBlckNhc2UoKSwgbG9jYXRpb24sIHRydWUpXG4gIHJlcXVlc3Quc2V0UmVxdWVzdEhlYWRlcihcIlgtUmVxdWVzdGVkLVdpdGhcIiwgXCJYTUxIdHRwUmVxdWVzdFwiKVxuXG4gIC8vIEFkZCB0aGUgcmVxdWVzdCBwYXlsb2FkIGlmIGF2YWlsYWJsZVxuICBpZiAob3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyAhPSB1bmRlZmluZWQgJiYgb3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyAhPSBcIlwiKSB7XG4gICAgLy8gU2VuZCB0aGUgcHJvcGVyIGhlYWRlciBpbmZvcm1hdGlvbiBhbG9uZyB3aXRoIHRoZSByZXF1ZXN0XG4gICAgcmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKFwiQ29udGVudC10eXBlXCIsIFwiYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkXCIpO1xuICB9XG5cbiAgcmVxdWVzdC5zZW5kKHJlcXVlc3RQYXlsb2FkKVxuXG4gIHJldHVybiByZXF1ZXN0XG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2ZvcmVhY2gtZWxzXCIpXG5cbnZhciBkZWZhdWx0U3dpdGNoZXMgPSByZXF1aXJlKFwiLi9zd2l0Y2hlc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKHN3aXRjaGVzLCBzd2l0Y2hlc09wdGlvbnMsIHNlbGVjdG9ycywgZnJvbUVsLCB0b0VsLCBvcHRpb25zKSB7XG4gIHNlbGVjdG9ycy5mb3JFYWNoKGZ1bmN0aW9uKHNlbGVjdG9yKSB7XG4gICAgdmFyIG5ld0VscyA9IGZyb21FbC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKVxuICAgIHZhciBvbGRFbHMgPSB0b0VsLnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpXG4gICAgaWYgKHRoaXMubG9nKSB7XG4gICAgICB0aGlzLmxvZyhcIlBqYXggc3dpdGNoXCIsIHNlbGVjdG9yLCBuZXdFbHMsIG9sZEVscylcbiAgICB9XG4gICAgaWYgKG5ld0Vscy5sZW5ndGggIT09IG9sZEVscy5sZW5ndGgpIHtcbiAgICAgIC8vIGZvckVhY2hFbHMobmV3RWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgLy8gICB0aGlzLmxvZyhcIm5ld0VsXCIsIGVsLCBlbC5vdXRlckhUTUwpXG4gICAgICAvLyB9LCB0aGlzKVxuICAgICAgLy8gZm9yRWFjaEVscyhvbGRFbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICAvLyAgIHRoaXMubG9nKFwib2xkRWxcIiwgZWwsIGVsLm91dGVySFRNTClcbiAgICAgIC8vIH0sIHRoaXMpXG4gICAgICB0aHJvdyBcIkRPTSBkb2VzbuKAmXQgbG9vayB0aGUgc2FtZSBvbiBuZXcgbG9hZGVkIHBhZ2U6IOKAmVwiICsgc2VsZWN0b3IgKyBcIuKAmSAtIG5ldyBcIiArIG5ld0Vscy5sZW5ndGggKyBcIiwgb2xkIFwiICsgb2xkRWxzLmxlbmd0aFxuICAgIH1cblxuICAgIGZvckVhY2hFbHMobmV3RWxzLCBmdW5jdGlvbihuZXdFbCwgaSkge1xuICAgICAgdmFyIG9sZEVsID0gb2xkRWxzW2ldXG4gICAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgICAgdGhpcy5sb2coXCJuZXdFbFwiLCBuZXdFbCwgXCJvbGRFbFwiLCBvbGRFbClcbiAgICAgIH1cbiAgICAgIGlmIChzd2l0Y2hlc1tzZWxlY3Rvcl0pIHtcbiAgICAgICAgc3dpdGNoZXNbc2VsZWN0b3JdLmJpbmQodGhpcykob2xkRWwsIG5ld0VsLCBvcHRpb25zLCBzd2l0Y2hlc09wdGlvbnNbc2VsZWN0b3JdKVxuICAgICAgfVxuICAgICAgZWxzZSB7XG4gICAgICAgIGRlZmF1bHRTd2l0Y2hlcy5vdXRlckhUTUwuYmluZCh0aGlzKShvbGRFbCwgbmV3RWwsIG9wdGlvbnMpXG4gICAgICB9XG4gICAgfSwgdGhpcylcbiAgfSwgdGhpcylcbn1cbiIsInZhciBvbiA9IHJlcXVpcmUoXCIuL2V2ZW50cy9vbi5qc1wiKVxuLy8gdmFyIG9mZiA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvb24uanNcIilcbi8vIHZhciB0cmlnZ2VyID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy90cmlnZ2VyLmpzXCIpXG5cblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gIG91dGVySFRNTDogZnVuY3Rpb24ob2xkRWwsIG5ld0VsKSB7XG4gICAgb2xkRWwub3V0ZXJIVE1MID0gbmV3RWwub3V0ZXJIVE1MXG4gICAgdGhpcy5vblN3aXRjaCgpXG4gIH0sXG5cbiAgaW5uZXJIVE1MOiBmdW5jdGlvbihvbGRFbCwgbmV3RWwpIHtcbiAgICBvbGRFbC5pbm5lckhUTUwgPSBuZXdFbC5pbm5lckhUTUxcbiAgICBvbGRFbC5jbGFzc05hbWUgPSBuZXdFbC5jbGFzc05hbWVcbiAgICB0aGlzLm9uU3dpdGNoKClcbiAgfSxcblxuICBzaWRlQnlTaWRlOiBmdW5jdGlvbihvbGRFbCwgbmV3RWwsIG9wdGlvbnMsIHN3aXRjaE9wdGlvbnMpIHtcbiAgICB2YXIgZm9yRWFjaCA9IEFycmF5LnByb3RvdHlwZS5mb3JFYWNoXG4gICAgdmFyIGVsc1RvUmVtb3ZlID0gW11cbiAgICB2YXIgZWxzVG9BZGQgPSBbXVxuICAgIHZhciBmcmFnVG9BcHBlbmQgPSBkb2N1bWVudC5jcmVhdGVEb2N1bWVudEZyYWdtZW50KClcbiAgICAvLyBoZWlnaHQgdHJhbnNpdGlvbiBhcmUgc2hpdHR5IG9uIHNhZmFyaVxuICAgIC8vIHNvIGNvbW1lbnRlZCBmb3Igbm93ICh1bnRpbCBJIGZvdW5kIHNvbWV0aGluZyA/KVxuICAgIC8vIHZhciByZWxldmFudEhlaWdodCA9IDBcbiAgICB2YXIgYW5pbWF0aW9uRXZlbnROYW1lcyA9IFwiYW5pbWF0aW9uZW5kIHdlYmtpdEFuaW1hdGlvbkVuZCBNU0FuaW1hdGlvbkVuZCBvYW5pbWF0aW9uZW5kXCJcbiAgICB2YXIgYW5pbWF0ZWRFbHNOdW1iZXIgPSAwXG4gICAgdmFyIHNleHlBbmltYXRpb25FbmQgPSBmdW5jdGlvbihlKSB7XG4gICAgICAgICAgaWYgKGUudGFyZ2V0ICE9IGUuY3VycmVudFRhcmdldCkge1xuICAgICAgICAgICAgLy8gZW5kIHRyaWdnZXJlZCBieSBhbiBhbmltYXRpb24gb24gYSBjaGlsZFxuICAgICAgICAgICAgcmV0dXJuXG4gICAgICAgICAgfVxuXG4gICAgICAgICAgYW5pbWF0ZWRFbHNOdW1iZXItLVxuICAgICAgICAgIGlmIChhbmltYXRlZEVsc051bWJlciA8PSAwICYmIGVsc1RvUmVtb3ZlKSB7XG4gICAgICAgICAgICBlbHNUb1JlbW92ZS5mb3JFYWNoKGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgICAgICAgIC8vIGJyb3dzaW5nIHF1aWNrbHkgY2FuIG1ha2UgdGhlIGVsXG4gICAgICAgICAgICAgIC8vIGFscmVhZHkgcmVtb3ZlZCBieSBsYXN0IHBhZ2UgdXBkYXRlID9cbiAgICAgICAgICAgICAgaWYgKGVsLnBhcmVudE5vZGUpIHtcbiAgICAgICAgICAgICAgICBlbC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKGVsKVxuICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KVxuXG4gICAgICAgICAgICBlbHNUb0FkZC5mb3JFYWNoKGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgICAgICAgIGVsLmNsYXNzTmFtZSA9IGVsLmNsYXNzTmFtZS5yZXBsYWNlKGVsLmdldEF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpLCBcIlwiKVxuICAgICAgICAgICAgICBlbC5yZW1vdmVBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKVxuICAgICAgICAgICAgICAvLyBQamF4Lm9mZihlbCwgYW5pbWF0aW9uRXZlbnROYW1lcywgc2V4eUFuaW1hdGlvbkVuZCwgdHJ1ZSlcbiAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgIGVsc1RvQWRkID0gbnVsbCAvLyBmcmVlIG1lbW9yeVxuICAgICAgICAgICAgZWxzVG9SZW1vdmUgPSBudWxsIC8vIGZyZWUgbWVtb3J5XG5cbiAgICAgICAgICAgIC8vIGFzc3VtZSB0aGUgaGVpZ2h0IGlzIG5vdyB1c2VsZXNzIChhdm9pZCBidWcgc2luY2UgdGhlcmUgaXMgb3ZlcmZsb3cgaGlkZGVuIG9uIHRoZSBwYXJlbnQpXG4gICAgICAgICAgICAvLyBvbGRFbC5zdHlsZS5oZWlnaHQgPSBcImF1dG9cIlxuXG4gICAgICAgICAgICAvLyB0aGlzIGlzIHRvIHRyaWdnZXIgc29tZSByZXBhaW50IChleGFtcGxlOiBwaWN0dXJlZmlsbClcbiAgICAgICAgICAgIHRoaXMub25Td2l0Y2goKVxuICAgICAgICAgICAgLy8gUGpheC50cmlnZ2VyKHdpbmRvdywgXCJzY3JvbGxcIilcbiAgICAgICAgICB9XG4gICAgICAgIH0uYmluZCh0aGlzKVxuXG4gICAgLy8gRm9yY2UgaGVpZ2h0IHRvIGJlIGFibGUgdG8gdHJpZ2dlciBjc3MgYW5pbWF0aW9uXG4gICAgLy8gaGVyZSB3ZSBnZXQgdGhlIHJlbGV2YW50IGhlaWdodFxuICAgIC8vIG9sZEVsLnBhcmVudE5vZGUuYXBwZW5kQ2hpbGQobmV3RWwpXG4gICAgLy8gcmVsZXZhbnRIZWlnaHQgPSBuZXdFbC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHRcbiAgICAvLyBvbGRFbC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKG5ld0VsKVxuICAgIC8vIG9sZEVsLnN0eWxlLmhlaWdodCA9IG9sZEVsLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpLmhlaWdodCArIFwicHhcIlxuXG4gICAgc3dpdGNoT3B0aW9ucyA9IHN3aXRjaE9wdGlvbnMgfHwge31cblxuICAgIGZvckVhY2guY2FsbChvbGRFbC5jaGlsZE5vZGVzLCBmdW5jdGlvbihlbCkge1xuICAgICAgZWxzVG9SZW1vdmUucHVzaChlbClcbiAgICAgIGlmIChlbC5jbGFzc0xpc3QgJiYgIWVsLmNsYXNzTGlzdC5jb250YWlucyhcImpzLVBqYXgtcmVtb3ZlXCIpKSB7XG4gICAgICAgIC8vIGZvciBmYXN0IHN3aXRjaCwgY2xlYW4gZWxlbWVudCB0aGF0IGp1c3QgaGF2ZSBiZWVuIGFkZGVkLCAmIG5vdCBjbGVhbmVkIHlldC5cbiAgICAgICAgaWYgKGVsLmhhc0F0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpKSB7XG4gICAgICAgICAgZWwuY2xhc3NOYW1lID0gZWwuY2xhc3NOYW1lLnJlcGxhY2UoZWwuZ2V0QXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIiksIFwiXCIpXG4gICAgICAgICAgZWwucmVtb3ZlQXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIilcbiAgICAgICAgfVxuICAgICAgICBlbC5jbGFzc0xpc3QuYWRkKFwianMtUGpheC1yZW1vdmVcIilcbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzICYmIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLnJlbW92ZUVsZW1lbnQpIHtcbiAgICAgICAgICBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5yZW1vdmVFbGVtZW50KGVsKVxuICAgICAgICB9XG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMpIHtcbiAgICAgICAgICBlbC5jbGFzc05hbWUgKz0gXCIgXCIgKyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMucmVtb3ZlICsgXCIgXCIgKyAob3B0aW9ucy5iYWNrd2FyZCA/IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5iYWNrd2FyZCA6IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5mb3J3YXJkKVxuICAgICAgICB9XG4gICAgICAgIGFuaW1hdGVkRWxzTnVtYmVyKytcbiAgICAgICAgb24oZWwsIGFuaW1hdGlvbkV2ZW50TmFtZXMsIHNleHlBbmltYXRpb25FbmQsIHRydWUpXG4gICAgICB9XG4gICAgfSlcblxuICAgIGZvckVhY2guY2FsbChuZXdFbC5jaGlsZE5vZGVzLCBmdW5jdGlvbihlbCkge1xuICAgICAgaWYgKGVsLmNsYXNzTGlzdCkge1xuICAgICAgICB2YXIgYWRkQ2xhc3NlcyA9IFwiXCJcbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcykge1xuICAgICAgICAgIGFkZENsYXNzZXMgPSBcIiBqcy1QamF4LWFkZCBcIiArIHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5hZGQgKyBcIiBcIiArIChvcHRpb25zLmJhY2t3YXJkID8gc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmZvcndhcmQgOiBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuYmFja3dhcmQpXG4gICAgICAgIH1cbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzICYmIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLmFkZEVsZW1lbnQpIHtcbiAgICAgICAgICBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5hZGRFbGVtZW50KGVsKVxuICAgICAgICB9XG4gICAgICAgIGVsLmNsYXNzTmFtZSArPSBhZGRDbGFzc2VzXG4gICAgICAgIGVsLnNldEF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIsIGFkZENsYXNzZXMpXG4gICAgICAgIGVsc1RvQWRkLnB1c2goZWwpXG4gICAgICAgIGZyYWdUb0FwcGVuZC5hcHBlbmRDaGlsZChlbClcbiAgICAgICAgYW5pbWF0ZWRFbHNOdW1iZXIrK1xuICAgICAgICBvbihlbCwgYW5pbWF0aW9uRXZlbnROYW1lcywgc2V4eUFuaW1hdGlvbkVuZCwgdHJ1ZSlcbiAgICAgIH1cbiAgICB9KVxuXG4gICAgLy8gcGFzcyBhbGwgY2xhc3NOYW1lIG9mIHRoZSBwYXJlbnRcbiAgICBvbGRFbC5jbGFzc05hbWUgPSBuZXdFbC5jbGFzc05hbWVcbiAgICBvbGRFbC5hcHBlbmRDaGlsZChmcmFnVG9BcHBlbmQpXG5cbiAgICAvLyBvbGRFbC5zdHlsZS5oZWlnaHQgPSByZWxldmFudEhlaWdodCArIFwicHhcIlxuICB9XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IChmdW5jdGlvbigpIHtcbiAgdmFyIGNvdW50ZXIgPSAwXG4gIHJldHVybiBmdW5jdGlvbigpIHtcbiAgICB2YXIgaWQgPSAoXCJwamF4XCIgKyAobmV3IERhdGUoKS5nZXRUaW1lKCkpKSArIFwiX1wiICsgY291bnRlclxuICAgIGNvdW50ZXIrK1xuICAgIHJldHVybiBpZFxuICB9XG59KSgpXG4iXX0=
