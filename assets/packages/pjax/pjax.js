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
    }.bind(this));

    return this;
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
    var collectForScriptcomplete = [];

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
      forEachEls(document.querySelectorAll(selector), function(el) {

        collectForScriptcomplete.push(executeScripts.call(this, el));

      }, this);

    },this);
    // }
    // catch(e) {
    //   if (this.options.debug) {
    //     this.log("Pjax switch fail: ", e)
    //   }
    //   this.switchFallback(tmpEl, document)
    // }

    Promise.all(collectForScriptcomplete).then(function(){
      document.dispatchEvent((new Event("pjax:scriptcomplete")));
    });

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
          if (this.options.forceRedirectOnFail) {
            this.latestChance(href);
          }
          throw e;
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
      script.async = true; // force asynchronous loading of peripheral js
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
  parent.removeChild(script)
  // avoid pollution only in head or body tags
  if (["head","body"].indexOf( parent.tagName.toLowerCase()) > 0) {
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
  this.options.forceRedirectOnFail = this.options.forceRedirectOnFail || false,
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
        head.appendChild(link);
    }
  }, this);

}

},{"./foreach-els":7}]},{},[1])(1)
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi4uLy4uL25vZGUvbGliL25vZGVfbW9kdWxlcy9icm93c2VyaWZ5L25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJpbmRleC5qcyIsImxpYi9jbG9uZS5qcyIsImxpYi9ldmFsLXNjcmlwdC5qcyIsImxpYi9ldmVudHMvb24uanMiLCJsaWIvZXZlbnRzL3RyaWdnZXIuanMiLCJsaWIvZXhlY3V0ZS1zY3JpcHRzLmpzIiwibGliL2ZvcmVhY2gtZWxzLmpzIiwibGliL2ZvcmVhY2gtc2VsZWN0b3JzLmpzIiwibGliL2lzLXN1cHBvcnRlZC5qcyIsImxpYi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQuanMiLCJsaWIvcHJvdG8vYXR0YWNoLWZvcm0uanMiLCJsaWIvcHJvdG8vYXR0YWNoLWxpbmsuanMiLCJsaWIvcHJvdG8vZ2V0LWVsZW1lbnRzLmpzIiwibGliL3Byb3RvL2xvZy5qcyIsImxpYi9wcm90by9wYXJzZS1kb20uanMiLCJsaWIvcHJvdG8vcGFyc2UtZWxlbWVudC5qcyIsImxpYi9wcm90by9wYXJzZS1vcHRpb25zLmpzIiwibGliL3Byb3RvL3JlZnJlc2guanMiLCJsaWIvcmVsb2FkLmpzIiwibGliL3JlcXVlc3QuanMiLCJsaWIvc3dpdGNoZXMtc2VsZWN0b3JzLmpzIiwibGliL3N3aXRjaGVzLmpzIiwibGliL3VuaXF1ZWlkLmpzIiwibGliL3VwZGF0ZS1zdHlsZXNoZWV0cy5qcyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQTtBQ0FBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN2UUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNqREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDL0JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzNCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNUQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwR0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pGQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNQQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN6Q0E7QUFDQTtBQUNBO0FBQ0E7O0FDSEE7QUFDQTtBQUNBO0FBQ0E7O0FDSEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25DQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkhBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNSQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwidmFyIGNsb25lID0gcmVxdWlyZSgnLi9saWIvY2xvbmUuanMnKVxudmFyIGV4ZWN1dGVTY3JpcHRzID0gcmVxdWlyZSgnLi9saWIvZXhlY3V0ZS1zY3JpcHRzLmpzJylcbnZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vbGliL2ZvcmVhY2gtZWxzLmpzXCIpXG52YXIgbmV3VWlkID0gcmVxdWlyZShcIi4vbGliL3VuaXF1ZWlkLmpzXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvb24uanNcIilcbi8vIHZhciBvZmYgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL29uLmpzXCIpXG52YXIgdHJpZ2dlciA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvdHJpZ2dlci5qc1wiKVxuXG5cbnZhciBQamF4ID0gZnVuY3Rpb24ob3B0aW9ucykge1xuICAgIHRoaXMuZmlyc3RydW4gPSB0cnVlXG5cbiAgICB2YXIgcGFyc2VPcHRpb25zID0gcmVxdWlyZShcIi4vbGliL3Byb3RvL3BhcnNlLW9wdGlvbnMuanNcIik7XG4gICAgcGFyc2VPcHRpb25zLmFwcGx5KHRoaXMsW29wdGlvbnNdKVxuICAgIHRoaXMubG9nKFwiUGpheCBvcHRpb25zXCIsIHRoaXMub3B0aW9ucylcblxuICAgIHRoaXMubWF4VWlkID0gdGhpcy5sYXN0VWlkID0gbmV3VWlkKClcblxuICAgIHRoaXMucGFyc2VET00oZG9jdW1lbnQpXG5cbiAgICBvbih3aW5kb3csIFwicG9wc3RhdGVcIiwgZnVuY3Rpb24oc3QpIHtcbiAgICAgIGlmIChzdC5zdGF0ZSkge1xuICAgICAgICB2YXIgb3B0ID0gY2xvbmUodGhpcy5vcHRpb25zKVxuICAgICAgICBvcHQudXJsID0gc3Quc3RhdGUudXJsXG4gICAgICAgIG9wdC50aXRsZSA9IHN0LnN0YXRlLnRpdGxlXG4gICAgICAgIG9wdC5oaXN0b3J5ID0gZmFsc2VcbiAgICAgICAgb3B0LnJlcXVlc3RPcHRpb25zID0ge307XG4gICAgICAgIGlmIChzdC5zdGF0ZS51aWQgPCB0aGlzLmxhc3RVaWQpIHtcbiAgICAgICAgICBvcHQuYmFja3dhcmQgPSB0cnVlXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgb3B0LmZvcndhcmQgPSB0cnVlXG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5sYXN0VWlkID0gc3Quc3RhdGUudWlkXG5cbiAgICAgICAgLy8gQHRvZG8gaW1wbGVtZW50IGhpc3RvcnkgY2FjaGUgaGVyZSwgYmFzZWQgb24gdWlkXG4gICAgICAgIHRoaXMubG9hZFVybChzdC5zdGF0ZS51cmwsIG9wdClcbiAgICAgIH1cbiAgICB9LmJpbmQodGhpcykpO1xuXG4gICAgcmV0dXJuIHRoaXM7XG4gIH1cblxuUGpheC5wcm90b3R5cGUgPSB7XG4gIGxvZzogcmVxdWlyZShcIi4vbGliL3Byb3RvL2xvZy5qc1wiKSxcblxuICBnZXRFbGVtZW50czogcmVxdWlyZShcIi4vbGliL3Byb3RvL2dldC1lbGVtZW50cy5qc1wiKSxcblxuICBwYXJzZURPTTogcmVxdWlyZShcIi4vbGliL3Byb3RvL3BhcnNlLWRvbS5qc1wiKSxcblxuICByZWZyZXNoOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vcmVmcmVzaC5qc1wiKSxcblxuICByZWxvYWQ6IHJlcXVpcmUoXCIuL2xpYi9yZWxvYWQuanNcIiksXG5cbiAgYXR0YWNoTGluazogcmVxdWlyZShcIi4vbGliL3Byb3RvL2F0dGFjaC1saW5rLmpzXCIpLFxuXG4gIGF0dGFjaEZvcm06IHJlcXVpcmUoXCIuL2xpYi9wcm90by9hdHRhY2gtZm9ybS5qc1wiKSxcblxuICB1cGRhdGVTdHlsZXNoZWV0czogcmVxdWlyZShcIi4vbGliL3VwZGF0ZS1zdHlsZXNoZWV0cy5qc1wiKSxcblxuICBmb3JFYWNoU2VsZWN0b3JzOiBmdW5jdGlvbihjYiwgY29udGV4dCwgRE9NY29udGV4dCkge1xuICAgIHJldHVybiByZXF1aXJlKFwiLi9saWIvZm9yZWFjaC1zZWxlY3RvcnMuanNcIikuYmluZCh0aGlzKSh0aGlzLm9wdGlvbnMuc2VsZWN0b3JzLCBjYiwgY29udGV4dCwgRE9NY29udGV4dClcbiAgfSxcblxuICBzd2l0Y2hTZWxlY3RvcnM6IGZ1bmN0aW9uKHNlbGVjdG9ycywgZnJvbUVsLCB0b0VsLCBvcHRpb25zKSB7XG4gICAgcmV0dXJuIHJlcXVpcmUoXCIuL2xpYi9zd2l0Y2hlcy1zZWxlY3RvcnMuanNcIikuYmluZCh0aGlzKSh0aGlzLm9wdGlvbnMuc3dpdGNoZXMsIHRoaXMub3B0aW9ucy5zd2l0Y2hlc09wdGlvbnMsIHNlbGVjdG9ycywgZnJvbUVsLCB0b0VsLCBvcHRpb25zKVxuICB9LFxuXG5cbiAgLy8gdG9vIG11Y2ggcHJvYmxlbSB3aXRoIHRoZSBjb2RlIGJlbG93XG4gIC8vICsgaXTigJlzIHRvbyBkYW5nZXJvdXNcbi8vICAgc3dpdGNoRmFsbGJhY2s6IGZ1bmN0aW9uKGZyb21FbCwgdG9FbCkge1xuLy8gICAgIHRoaXMuc3dpdGNoU2VsZWN0b3JzKFtcImhlYWRcIiwgXCJib2R5XCJdLCBmcm9tRWwsIHRvRWwpXG4vLyAgICAgLy8gZXhlY3V0ZSBzY3JpcHQgd2hlbiBET00gaXMgbGlrZSBpdCBzaG91bGQgYmVcbi8vICAgICBQamF4LmV4ZWN1dGVTY3JpcHRzKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXCJoZWFkXCIpKVxuLy8gICAgIFBqYXguZXhlY3V0ZVNjcmlwdHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcImJvZHlcIikpXG4vLyAgIH1cblxuICBsYXRlc3RDaGFuY2U6IGZ1bmN0aW9uKGhyZWYpIHtcbiAgICB3aW5kb3cubG9jYXRpb24gPSBocmVmXG4gIH0sXG5cbiAgb25Td2l0Y2g6IGZ1bmN0aW9uKCkge1xuICAgIHRyaWdnZXIod2luZG93LCBcInJlc2l6ZSBzY3JvbGxcIilcbiAgfSxcblxuICBsb2FkQ29udGVudDogZnVuY3Rpb24oaHRtbCwgb3B0aW9ucykge1xuICAgIHZhciB0bXBFbCA9IGRvY3VtZW50LmltcGxlbWVudGF0aW9uLmNyZWF0ZUhUTUxEb2N1bWVudChcInBqYXhcIilcbiAgICB2YXIgY29sbGVjdEZvclNjcmlwdGNvbXBsZXRlID0gW107XG5cbiAgICAvLyBwYXJzZSBIVE1MIGF0dHJpYnV0ZXMgdG8gY29weSB0aGVtXG4gICAgLy8gc2luY2Ugd2UgYXJlIGZvcmNlZCB0byB1c2UgZG9jdW1lbnRFbGVtZW50LmlubmVySFRNTCAob3V0ZXJIVE1MIGNhbid0IGJlIHVzZWQgZm9yIDxodG1sPilcbiAgICB2YXIgaHRtbFJlZ2V4ID0gLzxodG1sW14+XSs+L2dpXG4gICAgdmFyIGh0bWxBdHRyaWJzUmVnZXggPSAvXFxzP1thLXo6XSsoPzpcXD0oPzpcXCd8XFxcIilbXlxcJ1xcXCI+XSsoPzpcXCd8XFxcIikpKi9naVxuICAgIHZhciBtYXRjaGVzID0gaHRtbC5tYXRjaChodG1sUmVnZXgpXG4gICAgaWYgKG1hdGNoZXMgJiYgbWF0Y2hlcy5sZW5ndGgpIHtcbiAgICAgIG1hdGNoZXMgPSBtYXRjaGVzWzBdLm1hdGNoKGh0bWxBdHRyaWJzUmVnZXgpXG4gICAgICBpZiAobWF0Y2hlcy5sZW5ndGgpIHtcbiAgICAgICAgbWF0Y2hlcy5zaGlmdCgpXG4gICAgICAgIG1hdGNoZXMuZm9yRWFjaChmdW5jdGlvbihodG1sQXR0cmliKSB7XG4gICAgICAgICAgdmFyIGF0dHIgPSBodG1sQXR0cmliLnRyaW0oKS5zcGxpdChcIj1cIilcbiAgICAgICAgICBpZiAoYXR0ci5sZW5ndGggPT09IDEpIHtcbiAgICAgICAgICAgIHRtcEVsLmRvY3VtZW50RWxlbWVudC5zZXRBdHRyaWJ1dGUoYXR0clswXSwgdHJ1ZSlcbiAgICAgICAgICB9XG4gICAgICAgICAgZWxzZSB7XG4gICAgICAgICAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuc2V0QXR0cmlidXRlKGF0dHJbMF0sIGF0dHJbMV0uc2xpY2UoMSwgLTEpKVxuICAgICAgICAgIH1cbiAgICAgICAgfSlcbiAgICAgIH1cbiAgICB9XG5cbiAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuaW5uZXJIVE1MID0gaHRtbFxuICAgIHRoaXMubG9nKFwibG9hZCBjb250ZW50XCIsIHRtcEVsLmRvY3VtZW50RWxlbWVudC5hdHRyaWJ1dGVzLCB0bXBFbC5kb2N1bWVudEVsZW1lbnQuaW5uZXJIVE1MLmxlbmd0aClcblxuICAgIC8vIENsZWFyIG91dCBhbnkgZm9jdXNlZCBjb250cm9scyBiZWZvcmUgaW5zZXJ0aW5nIG5ldyBwYWdlIGNvbnRlbnRzLlxuICAgIC8vIHdlIGNsZWFyIGZvY3VzIG9uIG5vbiBmb3JtIGVsZW1lbnRzXG4gICAgaWYgKGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQgJiYgIWRvY3VtZW50LmFjdGl2ZUVsZW1lbnQudmFsdWUpIHtcbiAgICAgIHRyeSB7XG4gICAgICAgIGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQuYmx1cigpXG4gICAgICB9IGNhdGNoIChlKSB7IH1cbiAgICB9XG5cbiAgICB0aGlzLnN3aXRjaFNlbGVjdG9ycyh0aGlzLm9wdGlvbnMuc2VsZWN0b3JzLCB0bXBFbCwgZG9jdW1lbnQsIG9wdGlvbnMpXG5cbiAgICAvL3Jlc2V0IHN0eWxlc2hlZXRzIGlmIGFjdGl2YXRlZFxuICAgIGlmKHRoaXMub3B0aW9ucy5yZVJlbmRlckNTUyA9PT0gdHJ1ZSl7XG4gICAgICB0aGlzLnVwZGF0ZVN0eWxlc2hlZXRzLmNhbGwodGhpcywgdG1wRWwucXVlcnlTZWxlY3RvckFsbCgnbGlua1tyZWw9c3R5bGVzaGVldF0nKSwgZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbCgnbGlua1tyZWw9c3R5bGVzaGVldF0nKSk7XG4gICAgfVxuXG4gICAgLy8gRkYgYnVnOiBXb27igJl0IGF1dG9mb2N1cyBmaWVsZHMgdGhhdCBhcmUgaW5zZXJ0ZWQgdmlhIEpTLlxuICAgIC8vIFRoaXMgYmVoYXZpb3IgaXMgaW5jb3JyZWN0LiBTbyBpZiB0aGVyZXMgbm8gY3VycmVudCBmb2N1cywgYXV0b2ZvY3VzXG4gICAgLy8gdGhlIGxhc3QgZmllbGQuXG4gICAgLy9cbiAgICAvLyBodHRwOi8vd3d3LnczLm9yZy9odG1sL3dnL2RyYWZ0cy9odG1sL21hc3Rlci9mb3Jtcy5odG1sXG4gICAgdmFyIGF1dG9mb2N1c0VsID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChcIlthdXRvZm9jdXNdXCIpKS5wb3AoKVxuICAgIGlmIChhdXRvZm9jdXNFbCAmJiBkb2N1bWVudC5hY3RpdmVFbGVtZW50ICE9PSBhdXRvZm9jdXNFbCkge1xuICAgICAgYXV0b2ZvY3VzRWwuZm9jdXMoKTtcbiAgICB9XG5cbiAgICAvLyBleGVjdXRlIHNjcmlwdHMgd2hlbiBET00gaGF2ZSBiZWVuIGNvbXBsZXRlbHkgdXBkYXRlZFxuICAgIHRoaXMub3B0aW9ucy5zZWxlY3RvcnMuZm9yRWFjaCggZnVuY3Rpb24oc2VsZWN0b3IpIHtcbiAgICAgIGZvckVhY2hFbHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvciksIGZ1bmN0aW9uKGVsKSB7XG5cbiAgICAgICAgY29sbGVjdEZvclNjcmlwdGNvbXBsZXRlLnB1c2goZXhlY3V0ZVNjcmlwdHMuY2FsbCh0aGlzLCBlbCkpO1xuXG4gICAgICB9LCB0aGlzKTtcblxuICAgIH0sdGhpcyk7XG4gICAgLy8gfVxuICAgIC8vIGNhdGNoKGUpIHtcbiAgICAvLyAgIGlmICh0aGlzLm9wdGlvbnMuZGVidWcpIHtcbiAgICAvLyAgICAgdGhpcy5sb2coXCJQamF4IHN3aXRjaCBmYWlsOiBcIiwgZSlcbiAgICAvLyAgIH1cbiAgICAvLyAgIHRoaXMuc3dpdGNoRmFsbGJhY2sodG1wRWwsIGRvY3VtZW50KVxuICAgIC8vIH1cblxuICAgIFByb21pc2UuYWxsKGNvbGxlY3RGb3JTY3JpcHRjb21wbGV0ZSkudGhlbihmdW5jdGlvbigpe1xuICAgICAgZG9jdW1lbnQuZGlzcGF0Y2hFdmVudCgobmV3IEV2ZW50KFwicGpheDpzY3JpcHRjb21wbGV0ZVwiKSkpO1xuICAgIH0pO1xuXG4gIH0sXG5cbiAgZG9SZXF1ZXN0OiByZXF1aXJlKFwiLi9saWIvcmVxdWVzdC5qc1wiKSxcblxuICBsb2FkVXJsOiBmdW5jdGlvbihocmVmLCBvcHRpb25zKSB7XG4gICAgdGhpcy5sb2coXCJsb2FkIGhyZWZcIiwgaHJlZiwgb3B0aW9ucylcblxuICAgIHRyaWdnZXIoZG9jdW1lbnQsIFwicGpheDpzZW5kXCIsIG9wdGlvbnMpO1xuXG4gICAgLy8gRG8gdGhlIHJlcXVlc3RcbiAgICB0aGlzLmRvUmVxdWVzdChocmVmLCBvcHRpb25zLnJlcXVlc3RPcHRpb25zLCBmdW5jdGlvbihodG1sKSB7XG4gICAgICAvLyBGYWlsIGlmIHVuYWJsZSB0byBsb2FkIEhUTUwgdmlhIEFKQVhcbiAgICAgIGlmIChodG1sID09PSBmYWxzZSkge1xuICAgICAgICB0cmlnZ2VyKGRvY3VtZW50LFwicGpheDpjb21wbGV0ZSBwamF4OmVycm9yXCIsIG9wdGlvbnMpXG5cbiAgICAgICAgcmV0dXJuXG4gICAgICB9XG5cbiAgICAgIC8vIENsZWFyIG91dCBhbnkgZm9jdXNlZCBjb250cm9scyBiZWZvcmUgaW5zZXJ0aW5nIG5ldyBwYWdlIGNvbnRlbnRzLlxuICAgICAgZG9jdW1lbnQuYWN0aXZlRWxlbWVudC5ibHVyKClcblxuICAgICAgdHJ5IHtcbiAgICAgICAgdGhpcy5sb2FkQ29udGVudChodG1sLCBvcHRpb25zKVxuICAgICAgfVxuICAgICAgY2F0Y2ggKGUpIHtcbiAgICAgICAgaWYgKCF0aGlzLm9wdGlvbnMuZGVidWcpIHtcbiAgICAgICAgICBpZiAoY29uc29sZSAmJiBjb25zb2xlLmVycm9yKSB7XG4gICAgICAgICAgICBjb25zb2xlLmVycm9yKFwiUGpheCBzd2l0Y2ggZmFpbDogXCIsIGUpXG4gICAgICAgICAgfVxuICAgICAgICAgIHRoaXMubGF0ZXN0Q2hhbmNlKGhyZWYpXG4gICAgICAgICAgcmV0dXJuXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5mb3JjZVJlZGlyZWN0T25GYWlsKSB7XG4gICAgICAgICAgICB0aGlzLmxhdGVzdENoYW5jZShocmVmKTtcbiAgICAgICAgICB9XG4gICAgICAgICAgdGhyb3cgZTtcbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgICBpZiAob3B0aW9ucy5oaXN0b3J5KSB7XG4gICAgICAgIGlmICh0aGlzLmZpcnN0cnVuKSB7XG4gICAgICAgICAgdGhpcy5sYXN0VWlkID0gdGhpcy5tYXhVaWQgPSBuZXdVaWQoKVxuICAgICAgICAgIHRoaXMuZmlyc3RydW4gPSBmYWxzZVxuICAgICAgICAgIHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSh7XG4gICAgICAgICAgICB1cmw6IHdpbmRvdy5sb2NhdGlvbi5ocmVmLFxuICAgICAgICAgICAgdGl0bGU6IGRvY3VtZW50LnRpdGxlLFxuICAgICAgICAgICAgdWlkOiB0aGlzLm1heFVpZFxuICAgICAgICAgIH0sXG4gICAgICAgICAgZG9jdW1lbnQudGl0bGUpXG4gICAgICAgIH1cblxuICAgICAgICAvLyBVcGRhdGUgYnJvd3NlciBoaXN0b3J5XG4gICAgICAgIHRoaXMubGFzdFVpZCA9IHRoaXMubWF4VWlkID0gbmV3VWlkKClcbiAgICAgICAgd2luZG93Lmhpc3RvcnkucHVzaFN0YXRlKHtcbiAgICAgICAgICB1cmw6IGhyZWYsXG4gICAgICAgICAgdGl0bGU6IG9wdGlvbnMudGl0bGUsXG4gICAgICAgICAgdWlkOiB0aGlzLm1heFVpZFxuICAgICAgICB9LFxuICAgICAgICAgIG9wdGlvbnMudGl0bGUsXG4gICAgICAgICAgaHJlZilcbiAgICAgIH1cblxuICAgICAgdGhpcy5mb3JFYWNoU2VsZWN0b3JzKGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgIHRoaXMucGFyc2VET00oZWwpXG4gICAgICB9LCB0aGlzKVxuXG4gICAgICAvLyBGaXJlIEV2ZW50c1xuICAgICAgdHJpZ2dlcihkb2N1bWVudCxcInBqYXg6Y29tcGxldGUgcGpheDpzdWNjZXNzXCIsIG9wdGlvbnMpXG5cbiAgICAgIG9wdGlvbnMuYW5hbHl0aWNzKClcblxuICAgICAgLy8gU2Nyb2xsIHBhZ2UgdG8gdG9wIG9uIG5ldyBwYWdlIGxvYWRcbiAgICAgIGlmIChvcHRpb25zLnNjcm9sbFRvICE9PSBmYWxzZSkge1xuICAgICAgICBpZiAob3B0aW9ucy5zY3JvbGxUby5sZW5ndGggPiAxKSB7XG4gICAgICAgICAgd2luZG93LnNjcm9sbFRvKG9wdGlvbnMuc2Nyb2xsVG9bMF0sIG9wdGlvbnMuc2Nyb2xsVG9bMV0pXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgd2luZG93LnNjcm9sbFRvKDAsIG9wdGlvbnMuc2Nyb2xsVG8pXG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9LmJpbmQodGhpcykpXG4gIH1cbn1cblxuUGpheC5pc1N1cHBvcnRlZCA9IHJlcXVpcmUoXCIuL2xpYi9pcy1zdXBwb3J0ZWQuanNcIik7XG5cbi8vYXJndWFibHkgY291bGQgZG8gYGlmKCByZXF1aXJlKFwiLi9saWIvaXMtc3VwcG9ydGVkLmpzXCIpKCkpIHtgIGJ1dCB0aGF0IG1pZ2h0IGJlIGEgbGl0dGxlIHRvIHNpbXBsZVxuaWYgKFBqYXguaXNTdXBwb3J0ZWQoKSkge1xuICBtb2R1bGUuZXhwb3J0cyA9IFBqYXhcbn1cbi8vIGlmIHRoZXJlIGlzbuKAmXQgcmVxdWlyZWQgYnJvd3NlciBmdW5jdGlvbnMsIHJldHVybmluZyBzdHVwaWQgYXBpXG5lbHNlIHtcbiAgdmFyIHN0dXBpZFBqYXggPSBmdW5jdGlvbigpIHt9XG4gIGZvciAodmFyIGtleSBpbiBQamF4LnByb3RvdHlwZSkge1xuICAgIGlmIChQamF4LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eShrZXkpICYmIHR5cGVvZiBQamF4LnByb3RvdHlwZVtrZXldID09PSBcImZ1bmN0aW9uXCIpIHtcbiAgICAgIHN0dXBpZFBqYXhba2V5XSA9IHN0dXBpZFBqYXhcbiAgICB9XG4gIH1cblxuICBtb2R1bGUuZXhwb3J0cyA9IHN0dXBpZFBqYXhcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24ob2JqKSB7XG4gIGlmIChudWxsID09PSBvYmogfHwgXCJvYmplY3RcIiAhPSB0eXBlb2Ygb2JqKSB7XG4gICAgcmV0dXJuIG9ialxuICB9XG4gIHZhciBjb3B5ID0gb2JqLmNvbnN0cnVjdG9yKClcbiAgZm9yICh2YXIgYXR0ciBpbiBvYmopIHtcbiAgICBpZiAob2JqLmhhc093blByb3BlcnR5KGF0dHIpKSB7XG4gICAgICBjb3B5W2F0dHJdID0gb2JqW2F0dHJdXG4gICAgfVxuICB9XG4gIHJldHVybiBjb3B5XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciBxdWVyeVNlbGVjdG9yID0gdGhpcy5vcHRpb25zLm1haW5TY3JpcHRFbGVtZW50O1xuICB2YXIgY29kZSA9IChlbC50ZXh0IHx8IGVsLnRleHRDb250ZW50IHx8IGVsLmlubmVySFRNTCB8fCBcIlwiKVxuICB2YXIgc3JjID0gKGVsLnNyYyB8fCBcIlwiKTtcbiAgdmFyIHBhcmVudCA9IGVsLnBhcmVudE5vZGUgfHwgZG9jdW1lbnQucXVlcnlTZWxlY3RvcihxdWVyeVNlbGVjdG9yKSB8fCBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnRcbiAgdmFyIHNjcmlwdCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoXCJzY3JpcHRcIilcbiAgdmFyIHByb21pc2UgPSBudWxsO1xuXG4gIHRoaXMubG9nKFwiRXZhbHVhdGluZyBTY3JpcHQ6IFwiLCBlbCk7XG5cbiAgaWYgKGNvZGUubWF0Y2goXCJkb2N1bWVudC53cml0ZVwiKSkge1xuICAgIGlmIChjb25zb2xlICYmIGNvbnNvbGUubG9nKSB7XG4gICAgICBjb25zb2xlLmxvZyhcIlNjcmlwdCBjb250YWlucyBkb2N1bWVudC53cml0ZS4gQ2Fu4oCZdCBiZSBleGVjdXRlZCBjb3JyZWN0bHkuIENvZGUgc2tpcHBlZCBcIiwgZWwpXG4gICAgfVxuICAgIHJldHVybiBmYWxzZVxuICB9XG5cbiAgcHJvbWlzZSA9IG5ldyBQcm9taXNlKGZ1bmN0aW9uKHJlc29sdmUsIHJlamVjdCl7XG5cbiAgICBzY3JpcHQudHlwZSA9IFwidGV4dC9qYXZhc2NyaXB0XCJcbiAgICBpZiAoc3JjICE9IFwiXCIpIHtcbiAgICAgIHNjcmlwdC5zcmMgPSBzcmM7XG4gICAgICBzY3JpcHQub25sb2FkID0gcmVzb2x2ZTtcbiAgICAgIHNjcmlwdC5hc3luYyA9IHRydWU7IC8vIGZvcmNlIGFzeW5jaHJvbm91cyBsb2FkaW5nIG9mIHBlcmlwaGVyYWwganNcbiAgICB9XG5cbiAgICBpZiAoY29kZSAhPSBcIlwiKSB7XG4gICAgICB0cnkge1xuICAgICAgICBzY3JpcHQuYXBwZW5kQ2hpbGQoZG9jdW1lbnQuY3JlYXRlVGV4dE5vZGUoY29kZSkpXG4gICAgICB9XG4gICAgICBjYXRjaCAoZSkge1xuICAgICAgICAvLyBvbGQgSUVzIGhhdmUgZnVua3kgc2NyaXB0IG5vZGVzXG4gICAgICAgIHNjcmlwdC50ZXh0ID0gY29kZVxuICAgICAgfVxuICAgICAgcmVzb2x2ZSgpO1xuICAgIH1cbiAgfSk7XG5cbiAgdGhpcy5sb2coJ1BhcmVudEVsZW1lbnQgPT4gJywgcGFyZW50ICk7XG5cbiAgLy8gZXhlY3V0ZVxuICBwYXJlbnQuYXBwZW5kQ2hpbGQoc2NyaXB0KTtcbiAgcGFyZW50LnJlbW92ZUNoaWxkKHNjcmlwdClcbiAgLy8gYXZvaWQgcG9sbHV0aW9uIG9ubHkgaW4gaGVhZCBvciBib2R5IHRhZ3NcbiAgaWYgKFtcImhlYWRcIixcImJvZHlcIl0uaW5kZXhPZiggcGFyZW50LnRhZ05hbWUudG9Mb3dlckNhc2UoKSkgPiAwKSB7XG4gIH1cblxuICByZXR1cm4gcHJvbWlzZTtcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4uL2ZvcmVhY2gtZWxzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxzLCBldmVudHMsIGxpc3RlbmVyLCB1c2VDYXB0dXJlKSB7XG4gIGV2ZW50cyA9ICh0eXBlb2YgZXZlbnRzID09PSBcInN0cmluZ1wiID8gZXZlbnRzLnNwbGl0KFwiIFwiKSA6IGV2ZW50cylcblxuICBldmVudHMuZm9yRWFjaChmdW5jdGlvbihlKSB7XG4gICAgZm9yRWFjaEVscyhlbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICBlbC5hZGRFdmVudExpc3RlbmVyKGUsIGxpc3RlbmVyLCB1c2VDYXB0dXJlKVxuICAgIH0pXG4gIH0pXG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVscywgZXZlbnRzLCBvcHRzKSB7XG4gIGV2ZW50cyA9ICh0eXBlb2YgZXZlbnRzID09PSBcInN0cmluZ1wiID8gZXZlbnRzLnNwbGl0KFwiIFwiKSA6IGV2ZW50cylcblxuICBldmVudHMuZm9yRWFjaChmdW5jdGlvbihlKSB7XG4gICAgdmFyIGV2ZW50IC8vID0gbmV3IEN1c3RvbUV2ZW50KGUpIC8vIGRvZXNuJ3QgZXZlcnl3aGVyZSB5ZXRcbiAgICBldmVudCA9IGRvY3VtZW50LmNyZWF0ZUV2ZW50KFwiSFRNTEV2ZW50c1wiKVxuICAgIGV2ZW50LmluaXRFdmVudChlLCB0cnVlLCB0cnVlKVxuICAgIGV2ZW50LmV2ZW50TmFtZSA9IGVcbiAgICBpZiAob3B0cykge1xuICAgICAgT2JqZWN0LmtleXMob3B0cykuZm9yRWFjaChmdW5jdGlvbihrZXkpIHtcbiAgICAgICAgZXZlbnRba2V5XSA9IG9wdHNba2V5XVxuICAgICAgfSlcbiAgICB9XG5cbiAgICBmb3JFYWNoRWxzKGVscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIHZhciBkb21GaXggPSBmYWxzZVxuICAgICAgaWYgKCFlbC5wYXJlbnROb2RlICYmIGVsICE9PSBkb2N1bWVudCAmJiBlbCAhPT0gd2luZG93KSB7XG4gICAgICAgIC8vIFRIQU5LUyBZT1UgSUUgKDkvMTAvLzExIGNvbmNlcm5lZClcbiAgICAgICAgLy8gZGlzcGF0Y2hFdmVudCBkb2Vzbid0IHdvcmsgaWYgZWxlbWVudCBpcyBub3QgaW4gdGhlIGRvbVxuICAgICAgICBkb21GaXggPSB0cnVlXG4gICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZWwpXG4gICAgICB9XG4gICAgICBlbC5kaXNwYXRjaEV2ZW50KGV2ZW50KVxuICAgICAgaWYgKGRvbUZpeCkge1xuICAgICAgICBlbC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKGVsKVxuICAgICAgfVxuICAgIH0pXG4gIH0pXG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2ZvcmVhY2gtZWxzXCIpXG52YXIgZXZhbFNjcmlwdCA9IHJlcXVpcmUoXCIuL2V2YWwtc2NyaXB0XCIpXG4vLyBGaW5kcyBhbmQgZXhlY3V0ZXMgc2NyaXB0cyAodXNlZCBmb3IgbmV3bHkgYWRkZWQgZWxlbWVudHMpXG4vLyBOZWVkZWQgc2luY2UgaW5uZXJIVE1MIGRvZXMgbm90IHJ1biBzY3JpcHRzXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG5cbiAgdGhpcy5sb2coXCJFeGVjdXRpbmcgc2NyaXB0cyBmb3IgXCIsIGVsKTtcblxuICB2YXIgbG9hZGluZ1NjcmlwdHMgPSBbXTtcblxuICBpZihlbCA9PT0gdW5kZWZpbmVkKSByZXR1cm4gUHJvbWlzZS5yZXNvbHZlKCk7XG5cbiAgaWYgKGVsLnRhZ05hbWUudG9Mb3dlckNhc2UoKSA9PT0gXCJzY3JpcHRcIikge1xuICAgIGV2YWxTY3JpcHQuY2FsbCh0aGlzLCBlbCk7XG4gIH1cblxuICBmb3JFYWNoRWxzKGVsLnF1ZXJ5U2VsZWN0b3JBbGwoXCJzY3JpcHRcIiksIGZ1bmN0aW9uKHNjcmlwdCkge1xuICAgIGlmICghc2NyaXB0LnR5cGUgfHwgc2NyaXB0LnR5cGUudG9Mb3dlckNhc2UoKSA9PT0gXCJ0ZXh0L2phdmFzY3JpcHRcIikge1xuICAgICAgLy8gaWYgKHNjcmlwdC5wYXJlbnROb2RlKSB7XG4gICAgICAvLyAgIHNjcmlwdC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKHNjcmlwdClcbiAgICAgIC8vIH1cbiAgICAgIGxvYWRpbmdTY3JpcHRzLnB1c2goZXZhbFNjcmlwdC5jYWxsKHRoaXMsIHNjcmlwdCkpO1xuICAgIH1cbiAgfSwgdGhpcyk7XG5cbiAgcmV0dXJuIFByb21pc2UuYWxsKGxvYWRpbmdTY3JpcHRzKTtcbn1cbiIsIi8qIGdsb2JhbCBIVE1MQ29sbGVjdGlvbjogdHJ1ZSAqL1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVscywgZm4sIGNvbnRleHQpIHtcbiAgaWYgKGVscyBpbnN0YW5jZW9mIEhUTUxDb2xsZWN0aW9uIHx8IGVscyBpbnN0YW5jZW9mIE5vZGVMaXN0IHx8IGVscyBpbnN0YW5jZW9mIEFycmF5KSB7XG4gICAgcmV0dXJuIEFycmF5LnByb3RvdHlwZS5mb3JFYWNoLmNhbGwoZWxzLCBmbiwgY29udGV4dClcbiAgfVxuICAvLyBhc3N1bWUgc2ltcGxlIGRvbSBlbGVtZW50XG4gIHJldHVybiBmbi5jYWxsKGNvbnRleHQsIGVscylcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihzZWxlY3RvcnMsIGNiLCBjb250ZXh0LCBET01jb250ZXh0KSB7XG4gIERPTWNvbnRleHQgPSBET01jb250ZXh0IHx8IGRvY3VtZW50XG4gIHNlbGVjdG9ycy5mb3JFYWNoKGZ1bmN0aW9uKHNlbGVjdG9yKSB7XG4gICAgZm9yRWFjaEVscyhET01jb250ZXh0LnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpLCBjYiwgY29udGV4dClcbiAgfSlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oKSB7XG4gIC8vIEJvcnJvd2VkIHdob2xlc2FsZSBmcm9tIGh0dHBzOi8vZ2l0aHViLmNvbS9kZWZ1bmt0L2pxdWVyeS1wamF4XG4gIHJldHVybiB3aW5kb3cuaGlzdG9yeSAmJlxuICAgIHdpbmRvdy5oaXN0b3J5LnB1c2hTdGF0ZSAmJlxuICAgIHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSAmJlxuICAgIC8vIHB1c2hTdGF0ZSBpc27igJl0IHJlbGlhYmxlIG9uIGlPUyB1bnRpbCA1LlxuICAgICFuYXZpZ2F0b3IudXNlckFnZW50Lm1hdGNoKC8oKGlQb2R8aVBob25lfGlQYWQpLitcXGJPU1xccytbMS00XVxcRHxXZWJBcHBzXFwvLitDRk5ldHdvcmspLylcbn1cbiIsImlmICghRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQpIHtcbiAgRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQgPSBmdW5jdGlvbihvVGhpcykge1xuICAgIGlmICh0eXBlb2YgdGhpcyAhPT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICAvLyBjbG9zZXN0IHRoaW5nIHBvc3NpYmxlIHRvIHRoZSBFQ01BU2NyaXB0IDUgaW50ZXJuYWwgSXNDYWxsYWJsZSBmdW5jdGlvblxuICAgICAgdGhyb3cgbmV3IFR5cGVFcnJvcihcIkZ1bmN0aW9uLnByb3RvdHlwZS5iaW5kIC0gd2hhdCBpcyB0cnlpbmcgdG8gYmUgYm91bmQgaXMgbm90IGNhbGxhYmxlXCIpXG4gICAgfVxuXG4gICAgdmFyIGFBcmdzID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXJndW1lbnRzLCAxKVxuICAgIHZhciB0aGF0ID0gdGhpc1xuICAgIHZhciBGbm9vcCA9IGZ1bmN0aW9uKCkge31cbiAgICB2YXIgZkJvdW5kID0gZnVuY3Rpb24oKSB7XG4gICAgICByZXR1cm4gdGhhdC5hcHBseSh0aGlzIGluc3RhbmNlb2YgRm5vb3AgJiYgb1RoaXMgPyB0aGlzIDogb1RoaXMsIGFBcmdzLmNvbmNhdChBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMpKSlcbiAgICB9XG5cbiAgICBGbm9vcC5wcm90b3R5cGUgPSB0aGlzLnByb3RvdHlwZVxuICAgIGZCb3VuZC5wcm90b3R5cGUgPSBuZXcgRm5vb3AoKVxuXG4gICAgcmV0dXJuIGZCb3VuZFxuICB9XG59XG4iLCJyZXF1aXJlKFwiLi4vcG9seWZpbGxzL0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuLi9ldmVudHMvb25cIilcbnZhciBjbG9uZSA9IHJlcXVpcmUoXCIuLi9jbG9uZVwiKVxuXG52YXIgYXR0ckNsaWNrID0gXCJkYXRhLXBqYXgtY2xpY2stc3RhdGVcIlxuXG52YXIgZm9ybUFjdGlvbiA9IGZ1bmN0aW9uKGVsLCBldmVudCl7XG5cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zID0ge1xuICAgIHJlcXVlc3RVcmwgOiBlbC5nZXRBdHRyaWJ1dGUoJ2FjdGlvbicpIHx8IHdpbmRvdy5sb2NhdGlvbi5ocmVmLFxuICAgIHJlcXVlc3RNZXRob2QgOiBlbC5nZXRBdHRyaWJ1dGUoJ21ldGhvZCcpIHx8ICdHRVQnLFxuICB9XG5cbiAgLy9jcmVhdGUgYSB0ZXN0YWJsZSB2aXJ0dWFsIGxpbmsgb2YgdGhlIGZvcm0gYWN0aW9uXG4gIHZhciB2aXJ0TGlua0VsZW1lbnQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdhJyk7XG4gIHZpcnRMaW5rRWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2hyZWYnLCB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFVybCk7XG5cbiAgLy8gSWdub3JlIGV4dGVybmFsIGxpbmtzLlxuICBpZiAodmlydExpbmtFbGVtZW50LnByb3RvY29sICE9PSB3aW5kb3cubG9jYXRpb24ucHJvdG9jb2wgfHwgdmlydExpbmtFbGVtZW50Lmhvc3QgIT09IHdpbmRvdy5sb2NhdGlvbi5ob3N0KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJleHRlcm5hbFwiKTtcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBjbGljayBpZiB3ZSBhcmUgb24gYW4gYW5jaG9yIG9uIHRoZSBzYW1lIHBhZ2VcbiAgaWYgKHZpcnRMaW5rRWxlbWVudC5wYXRobmFtZSA9PT0gd2luZG93LmxvY2F0aW9uLnBhdGhuYW1lICYmIHZpcnRMaW5rRWxlbWVudC5oYXNoLmxlbmd0aCA+IDApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1wcmVzZW50XCIpO1xuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGVtcHR5IGFuY2hvciBcImZvby5odG1sI1wiXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdICsgXCIjXCIpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1lbXB0eVwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gaWYgZGVjbGFyZWQgYXMgYSBmdWxsIHJlbG9hZCwganVzdCBub3JtYWxseSBzdWJtaXQgdGhlIGZvcm1cbiAgaWYgKCB0aGlzLm9wdGlvbnMuY3VycmVudFVybEZ1bGxSZWxvYWQpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInJlbG9hZFwiKTtcbiAgICByZXR1cm47XG4gIH1cblxuICBldmVudC5wcmV2ZW50RGVmYXVsdCgpXG4gIHZhciBuYW1lTGlzdCA9IFtdO1xuICB2YXIgcGFyYW1PYmplY3QgPSBbXTtcbiAgZm9yKHZhciBlbGVtZW50S2V5IGluIGVsLmVsZW1lbnRzKSB7XG4gICAgdmFyIGVsZW1lbnQgPSBlbC5lbGVtZW50c1tlbGVtZW50S2V5XTtcbiAgICBpZiAoISFlbGVtZW50Lm5hbWUgJiYgZWxlbWVudC5hdHRyaWJ1dGVzICE9PSB1bmRlZmluZWQgJiYgZWxlbWVudC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgIT09ICdidXR0b24nKXtcbiAgICAgIGlmIChcbiAgICAgICAgKGVsZW1lbnQudHlwZSAhPT0gJ2NoZWNrYm94JyAmJiBlbGVtZW50LnR5cGUgIT09ICdyYWRpbycpIHx8IGVsZW1lbnQuY2hlY2tlZFxuICAgICAgKSB7XG4gICAgICAgIGlmKG5hbWVMaXN0LmluZGV4T2YoZWxlbWVudC5uYW1lKSA9PT0gLTEpe1xuICAgICAgICAgIG5hbWVMaXN0LnB1c2goZWxlbWVudC5uYW1lKTtcbiAgICAgICAgICBwYXJhbU9iamVjdC5wdXNoKHsgbmFtZTogZW5jb2RlVVJJQ29tcG9uZW50KGVsZW1lbnQubmFtZSksIHZhbHVlOiBlbmNvZGVVUklDb21wb25lbnQoZWxlbWVudC52YWx1ZSl9KTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfVxuXG5cblxuICAvL0NyZWF0aW5nIGEgZ2V0U3RyaW5nXG4gIHZhciBwYXJhbXNTdHJpbmcgPSAocGFyYW1PYmplY3QubWFwKGZ1bmN0aW9uKHZhbHVlKXtyZXR1cm4gdmFsdWUubmFtZStcIj1cIit2YWx1ZS52YWx1ZTt9KSkuam9pbignJicpO1xuXG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0UGF5bG9hZCA9IHBhcmFtT2JqZWN0O1xuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgPSBwYXJhbXNTdHJpbmc7XG5cbiAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJzdWJtaXRcIik7XG5cbiAgdGhpcy5sb2FkVXJsKHZpcnRMaW5rRWxlbWVudC5ocmVmLCBjbG9uZSh0aGlzLm9wdGlvbnMpKVxuXG59O1xuXG52YXIgaXNEZWZhdWx0UHJldmVudGVkID0gZnVuY3Rpb24oZXZlbnQpIHtcbiAgcmV0dXJuIGV2ZW50LmRlZmF1bHRQcmV2ZW50ZWQgfHwgZXZlbnQucmV0dXJuVmFsdWUgPT09IGZhbHNlO1xufTtcblxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciB0aGF0ID0gdGhpc1xuXG4gIG9uKGVsLCBcInN1Ym1pdFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBmb3JtQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICB9KVxuXG4gIG9uKGVsLCBcImtleXVwXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuXG4gICAgaWYgKGV2ZW50LmtleUNvZGUgPT0gMTMpIHtcbiAgICAgIGZvcm1BY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gICAgfVxuICB9LmJpbmQodGhpcykpXG59XG4iLCJyZXF1aXJlKFwiLi4vcG9seWZpbGxzL0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuLi9ldmVudHMvb25cIilcbnZhciBjbG9uZSA9IHJlcXVpcmUoXCIuLi9jbG9uZVwiKVxuXG52YXIgYXR0ckNsaWNrID0gXCJkYXRhLXBqYXgtY2xpY2stc3RhdGVcIlxudmFyIGF0dHJLZXkgPSBcImRhdGEtcGpheC1rZXl1cC1zdGF0ZVwiXG5cbnZhciBsaW5rQWN0aW9uID0gZnVuY3Rpb24oZWwsIGV2ZW50KSB7XG4gIC8vIERvbuKAmXQgYnJlYWsgYnJvd3NlciBzcGVjaWFsIGJlaGF2aW9yIG9uIGxpbmtzIChsaWtlIHBhZ2UgaW4gbmV3IHdpbmRvdylcbiAgaWYgKGV2ZW50LndoaWNoID4gMSB8fCBldmVudC5tZXRhS2V5IHx8IGV2ZW50LmN0cmxLZXkgfHwgZXZlbnQuc2hpZnRLZXkgfHwgZXZlbnQuYWx0S2V5KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJtb2RpZmllclwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gd2UgZG8gdGVzdCBvbiBocmVmIG5vdyB0byBwcmV2ZW50IHVuZXhwZWN0ZWQgYmVoYXZpb3IgaWYgZm9yIHNvbWUgcmVhc29uXG4gIC8vIHVzZXIgaGF2ZSBocmVmIHRoYXQgY2FuIGJlIGR5bmFtaWNhbGx5IHVwZGF0ZWRcblxuICAvLyBJZ25vcmUgZXh0ZXJuYWwgbGlua3MuXG4gIGlmIChlbC5wcm90b2NvbCAhPT0gd2luZG93LmxvY2F0aW9uLnByb3RvY29sIHx8IGVsLmhvc3QgIT09IHdpbmRvdy5sb2NhdGlvbi5ob3N0KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJleHRlcm5hbFwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGNsaWNrIGlmIHdlIGFyZSBvbiBhbiBhbmNob3Igb24gdGhlIHNhbWUgcGFnZVxuICBpZiAoZWwucGF0aG5hbWUgPT09IHdpbmRvdy5sb2NhdGlvbi5wYXRobmFtZSAmJiBlbC5oYXNoLmxlbmd0aCA+IDApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1wcmVzZW50XCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgYW5jaG9ycyBvbiB0aGUgc2FtZSBwYWdlIChrZWVwIG5hdGl2ZSBiZWhhdmlvcilcbiAgaWYgKGVsLmhhc2ggJiYgZWwuaHJlZi5yZXBsYWNlKGVsLmhhc2gsIFwiXCIpID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5yZXBsYWNlKGxvY2F0aW9uLmhhc2gsIFwiXCIpKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3JcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBlbXB0eSBhbmNob3IgXCJmb28uaHRtbCNcIlxuICBpZiAoZWwuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdICsgXCIjXCIpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1lbXB0eVwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgZXZlbnQucHJldmVudERlZmF1bHQoKVxuXG4gIC8vIGRvbuKAmXQgZG8gXCJub3RoaW5nXCIgaWYgdXNlciB0cnkgdG8gcmVsb2FkIHRoZSBwYWdlIGJ5IGNsaWNraW5nIHRoZSBzYW1lIGxpbmsgdHdpY2VcbiAgaWYgKFxuICAgIHRoaXMub3B0aW9ucy5jdXJyZW50VXJsRnVsbFJlbG9hZCAmJlxuICAgIGVsLmhyZWYgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnNwbGl0KFwiI1wiKVswXVxuICApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInJlbG9hZFwiKVxuICAgIHRoaXMucmVsb2FkKClcbiAgICByZXR1cm5cbiAgfVxuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgPSB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgfHwge307XG4gIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwibG9hZFwiKVxuICB0aGlzLmxvYWRVcmwoZWwuaHJlZiwgY2xvbmUodGhpcy5vcHRpb25zKSlcbn1cblxudmFyIGlzRGVmYXVsdFByZXZlbnRlZCA9IGZ1bmN0aW9uKGV2ZW50KSB7XG4gIHJldHVybiBldmVudC5kZWZhdWx0UHJldmVudGVkIHx8IGV2ZW50LnJldHVyblZhbHVlID09PSBmYWxzZTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB2YXIgdGhhdCA9IHRoaXNcblxuICBvbihlbCwgXCJjbGlja1wiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBsaW5rQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICB9KVxuXG4gIG9uKGVsLCBcImtleXVwXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIC8vIERvbuKAmXQgYnJlYWsgYnJvd3NlciBzcGVjaWFsIGJlaGF2aW9yIG9uIGxpbmtzIChsaWtlIHBhZ2UgaW4gbmV3IHdpbmRvdylcbiAgICBpZiAoZXZlbnQud2hpY2ggPiAxIHx8IGV2ZW50Lm1ldGFLZXkgfHwgZXZlbnQuY3RybEtleSB8fCBldmVudC5zaGlmdEtleSB8fCBldmVudC5hbHRLZXkpIHtcbiAgICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyS2V5LCBcIm1vZGlmaWVyXCIpXG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBpZiAoZXZlbnQua2V5Q29kZSA9PSAxMykge1xuICAgICAgbGlua0FjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgICB9XG4gIH0uYmluZCh0aGlzKSlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgcmV0dXJuIGVsLnF1ZXJ5U2VsZWN0b3JBbGwodGhpcy5vcHRpb25zLmVsZW1lbnRzKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbigpIHtcbiAgaWYgKCh0aGlzLm9wdGlvbnMuZGVidWcgJiYgY29uc29sZSkpIHtcbiAgICBpZiAodHlwZW9mIGNvbnNvbGUubG9nID09PSBcImZ1bmN0aW9uXCIpIHtcbiAgICAgIGNvbnNvbGUubG9nLmFwcGx5KGNvbnNvbGUsIGFyZ3VtZW50cyk7XG4gICAgfVxuICAgIC8vIGllIGlzIHdlaXJkXG4gICAgZWxzZSBpZiAoY29uc29sZS5sb2cpIHtcbiAgICAgIGNvbnNvbGUubG9nKGFyZ3VtZW50cyk7XG4gICAgfVxuICB9XG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG52YXIgcGFyc2VFbGVtZW50ID0gcmVxdWlyZShcIi4vcGFyc2UtZWxlbWVudFwiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIGZvckVhY2hFbHModGhpcy5nZXRFbGVtZW50cyhlbCksIHBhcnNlRWxlbWVudCwgdGhpcylcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgc3dpdGNoIChlbC50YWdOYW1lLnRvTG93ZXJDYXNlKCkpIHtcbiAgY2FzZSBcImFcIjpcbiAgICAvLyBvbmx5IGF0dGFjaCBsaW5rIGlmIGVsIGRvZXMgbm90IGFscmVhZHkgaGF2ZSBsaW5rIGF0dGFjaGVkXG4gICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICB0aGlzLmF0dGFjaExpbmsoZWwpXG4gICAgfVxuICAgIGJyZWFrXG5cbiAgICBjYXNlIFwiZm9ybVwiOlxuICAgICAgLy8gb25seSBhdHRhY2ggbGluayBpZiBlbCBkb2VzIG5vdCBhbHJlYWR5IGhhdmUgbGluayBhdHRhY2hlZFxuICAgICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICAgIHRoaXMuYXR0YWNoRm9ybShlbClcbiAgICAgIH1cbiAgICBicmVha1xuXG4gIGRlZmF1bHQ6XG4gICAgdGhyb3cgXCJQamF4IGNhbiBvbmx5IGJlIGFwcGxpZWQgb24gPGE+IG9yIDxmb3JtPiBzdWJtaXRcIlxuICB9XG59XG4iLCIvKiBnbG9iYWwgX2dhcTogdHJ1ZSwgZ2E6IHRydWUgKi9cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihvcHRpb25zKXtcbiAgdGhpcy5vcHRpb25zID0gb3B0aW9uc1xuICB0aGlzLm9wdGlvbnMuZWxlbWVudHMgPSB0aGlzLm9wdGlvbnMuZWxlbWVudHMgfHwgXCJhW2hyZWZdLCBmb3JtW2FjdGlvbl1cIixcbiAgdGhpcy5vcHRpb25zLnJlUmVuZGVyQ1NTID0gdGhpcy5vcHRpb25zLnJlUmVuZGVyQ1NTIHx8IGZhbHNlLFxuICB0aGlzLm9wdGlvbnMuZm9yY2VSZWRpcmVjdE9uRmFpbCA9IHRoaXMub3B0aW9ucy5mb3JjZVJlZGlyZWN0T25GYWlsIHx8IGZhbHNlLFxuICB0aGlzLm9wdGlvbnMubWFpblNjcmlwdEVsZW1lbnQgPSB0aGlzLm9wdGlvbnMubWFpblNjcmlwdEVsZW1lbnQgfHwgXCJoZWFkXCJcbiAgdGhpcy5vcHRpb25zLnNlbGVjdG9ycyA9IHRoaXMub3B0aW9ucy5zZWxlY3RvcnMgfHwgW1widGl0bGVcIiwgXCIuanMtUGpheFwiXVxuICB0aGlzLm9wdGlvbnMuc3dpdGNoZXMgPSB0aGlzLm9wdGlvbnMuc3dpdGNoZXMgfHwge31cbiAgdGhpcy5vcHRpb25zLnN3aXRjaGVzT3B0aW9ucyA9IHRoaXMub3B0aW9ucy5zd2l0Y2hlc09wdGlvbnMgfHwge31cbiAgdGhpcy5vcHRpb25zLmhpc3RvcnkgPSB0aGlzLm9wdGlvbnMuaGlzdG9yeSB8fCB0cnVlXG4gIHRoaXMub3B0aW9ucy5hbmFseXRpY3MgPSB0aGlzLm9wdGlvbnMuYW5hbHl0aWNzIHx8IGZ1bmN0aW9uKCkge1xuICAgIC8vIG9wdGlvbnMuYmFja3dhcmQgb3Igb3B0aW9ucy5mb3dhcmQgY2FuIGJlIHRydWUgb3IgdW5kZWZpbmVkXG4gICAgLy8gYnkgZGVmYXVsdCwgd2UgZG8gdHJhY2sgYmFjay9mb3dhcmQgaGl0XG4gICAgLy8gaHR0cHM6Ly9wcm9kdWN0Zm9ydW1zLmdvb2dsZS5jb20vZm9ydW0vIyF0b3BpYy9hbmFseXRpY3MvV1Z3TURqTGhYWWtcbiAgICBpZiAod2luZG93Ll9nYXEpIHtcbiAgICAgIF9nYXEucHVzaChbXCJfdHJhY2tQYWdldmlld1wiXSlcbiAgICB9XG4gICAgaWYgKHdpbmRvdy5nYSkge1xuICAgICAgZ2EoXCJzZW5kXCIsIFwicGFnZXZpZXdcIiwge3BhZ2U6IGxvY2F0aW9uLnBhdGhuYW1lLCB0aXRsZTogZG9jdW1lbnQudGl0bGV9KVxuICAgIH1cbiAgfVxuICB0aGlzLm9wdGlvbnMuc2Nyb2xsVG8gPSAodHlwZW9mIHRoaXMub3B0aW9ucy5zY3JvbGxUbyA9PT0gJ3VuZGVmaW5lZCcpID8gMCA6IHRoaXMub3B0aW9ucy5zY3JvbGxUbztcbiAgdGhpcy5vcHRpb25zLmNhY2hlQnVzdCA9ICh0eXBlb2YgdGhpcy5vcHRpb25zLmNhY2hlQnVzdCA9PT0gJ3VuZGVmaW5lZCcpID8gdHJ1ZSA6IHRoaXMub3B0aW9ucy5jYWNoZUJ1c3RcbiAgdGhpcy5vcHRpb25zLmRlYnVnID0gdGhpcy5vcHRpb25zLmRlYnVnIHx8IGZhbHNlXG5cbiAgLy8gd2UgY2Fu4oCZdCByZXBsYWNlIGJvZHkub3V0ZXJIVE1MIG9yIGhlYWQub3V0ZXJIVE1MXG4gIC8vIGl0IGNyZWF0ZSBhIGJ1ZyB3aGVyZSBuZXcgYm9keSBvciBuZXcgaGVhZCBhcmUgY3JlYXRlZCBpbiB0aGUgZG9tXG4gIC8vIGlmIHlvdSBzZXQgaGVhZC5vdXRlckhUTUwsIGEgbmV3IGJvZHkgdGFnIGlzIGFwcGVuZGVkLCBzbyB0aGUgZG9tIGdldCAyIGJvZHlcbiAgLy8gJiBpdCBicmVhayB0aGUgc3dpdGNoRmFsbGJhY2sgd2hpY2ggcmVwbGFjZSBoZWFkICYgYm9keVxuICBpZiAoIXRoaXMub3B0aW9ucy5zd2l0Y2hlcy5oZWFkKSB7XG4gICAgdGhpcy5vcHRpb25zLnN3aXRjaGVzLmhlYWQgPSB0aGlzLnN3aXRjaEVsZW1lbnRzQWx0XG4gIH1cbiAgaWYgKCF0aGlzLm9wdGlvbnMuc3dpdGNoZXMuYm9keSkge1xuICAgIHRoaXMub3B0aW9ucy5zd2l0Y2hlcy5ib2R5ID0gdGhpcy5zd2l0Y2hFbGVtZW50c0FsdFxuICB9XG4gIGlmICh0eXBlb2Ygb3B0aW9ucy5hbmFseXRpY3MgIT09IFwiZnVuY3Rpb25cIikge1xuICAgIG9wdGlvbnMuYW5hbHl0aWNzID0gZnVuY3Rpb24oKSB7fVxuICB9XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHRoaXMucGFyc2VET00oZWwgfHwgZG9jdW1lbnQpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKCkge1xuICB3aW5kb3cubG9jYXRpb24ucmVsb2FkKClcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24obG9jYXRpb24sIG9wdGlvbnMsIGNhbGxiYWNrKSB7XG4gIG9wdGlvbnMgPSBvcHRpb25zIHx8IHt9O1xuICB2YXIgcmVxdWVzdE1ldGhvZCA9IG9wdGlvbnMucmVxdWVzdE1ldGhvZCB8fCBcIkdFVFwiO1xuICB2YXIgcmVxdWVzdFBheWxvYWQgPSBvcHRpb25zLnJlcXVlc3RQYXlsb2FkU3RyaW5nIHx8IG51bGw7XG4gIHZhciByZXF1ZXN0ID0gbmV3IFhNTEh0dHBSZXF1ZXN0KClcblxuICByZXF1ZXN0Lm9ucmVhZHlzdGF0ZWNoYW5nZSA9IGZ1bmN0aW9uKCkge1xuICAgIGlmIChyZXF1ZXN0LnJlYWR5U3RhdGUgPT09IDQpIHtcbiAgICAgIGlmIChyZXF1ZXN0LnN0YXR1cyA9PT0gMjAwKSB7XG4gICAgICAgIGNhbGxiYWNrKHJlcXVlc3QucmVzcG9uc2VUZXh0LCByZXF1ZXN0KVxuICAgICAgfVxuICAgICAgZWxzZSB7XG4gICAgICAgIGNhbGxiYWNrKG51bGwsIHJlcXVlc3QpXG4gICAgICB9XG4gICAgfVxuICB9XG5cbiAgLy8gQWRkIGEgdGltZXN0YW1wIGFzIHBhcnQgb2YgdGhlIHF1ZXJ5IHN0cmluZyBpZiBjYWNoZSBidXN0aW5nIGlzIGVuYWJsZWRcbiAgaWYgKHRoaXMub3B0aW9ucy5jYWNoZUJ1c3QpIHtcbiAgICBsb2NhdGlvbiArPSAoIS9bPyZdLy50ZXN0KGxvY2F0aW9uKSA/IFwiP1wiIDogXCImXCIpICsgbmV3IERhdGUoKS5nZXRUaW1lKClcbiAgfVxuXG4gIHJlcXVlc3Qub3BlbihyZXF1ZXN0TWV0aG9kLnRvVXBwZXJDYXNlKCksIGxvY2F0aW9uLCB0cnVlKVxuICByZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoXCJYLVJlcXVlc3RlZC1XaXRoXCIsIFwiWE1MSHR0cFJlcXVlc3RcIilcblxuICAvLyBBZGQgdGhlIHJlcXVlc3QgcGF5bG9hZCBpZiBhdmFpbGFibGVcbiAgaWYgKG9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgIT0gdW5kZWZpbmVkICYmIG9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgIT0gXCJcIikge1xuICAgIC8vIFNlbmQgdGhlIHByb3BlciBoZWFkZXIgaW5mb3JtYXRpb24gYWxvbmcgd2l0aCB0aGUgcmVxdWVzdFxuICAgIHJlcXVlc3Quc2V0UmVxdWVzdEhlYWRlcihcIkNvbnRlbnQtdHlwZVwiLCBcImFwcGxpY2F0aW9uL3gtd3d3LWZvcm0tdXJsZW5jb2RlZFwiKTtcbiAgfVxuXG4gIHJlcXVlc3Quc2VuZChyZXF1ZXN0UGF5bG9hZClcblxuICByZXR1cm4gcmVxdWVzdFxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9mb3JlYWNoLWVsc1wiKVxuXG52YXIgZGVmYXVsdFN3aXRjaGVzID0gcmVxdWlyZShcIi4vc3dpdGNoZXNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihzd2l0Y2hlcywgc3dpdGNoZXNPcHRpb25zLCBzZWxlY3RvcnMsIGZyb21FbCwgdG9FbCwgb3B0aW9ucykge1xuICBzZWxlY3RvcnMuZm9yRWFjaChmdW5jdGlvbihzZWxlY3Rvcikge1xuICAgIHZhciBuZXdFbHMgPSBmcm9tRWwucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcilcbiAgICB2YXIgb2xkRWxzID0gdG9FbC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKVxuICAgIGlmICh0aGlzLmxvZykge1xuICAgICAgdGhpcy5sb2coXCJQamF4IHN3aXRjaFwiLCBzZWxlY3RvciwgbmV3RWxzLCBvbGRFbHMpXG4gICAgfVxuICAgIGlmIChuZXdFbHMubGVuZ3RoICE9PSBvbGRFbHMubGVuZ3RoKSB7XG4gICAgICAvLyBmb3JFYWNoRWxzKG5ld0VscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIC8vICAgdGhpcy5sb2coXCJuZXdFbFwiLCBlbCwgZWwub3V0ZXJIVE1MKVxuICAgICAgLy8gfSwgdGhpcylcbiAgICAgIC8vIGZvckVhY2hFbHMob2xkRWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgLy8gICB0aGlzLmxvZyhcIm9sZEVsXCIsIGVsLCBlbC5vdXRlckhUTUwpXG4gICAgICAvLyB9LCB0aGlzKVxuICAgICAgdGhyb3cgXCJET00gZG9lc27igJl0IGxvb2sgdGhlIHNhbWUgb24gbmV3IGxvYWRlZCBwYWdlOiDigJlcIiArIHNlbGVjdG9yICsgXCLigJkgLSBuZXcgXCIgKyBuZXdFbHMubGVuZ3RoICsgXCIsIG9sZCBcIiArIG9sZEVscy5sZW5ndGhcbiAgICB9XG5cbiAgICBmb3JFYWNoRWxzKG5ld0VscywgZnVuY3Rpb24obmV3RWwsIGkpIHtcbiAgICAgIHZhciBvbGRFbCA9IG9sZEVsc1tpXVxuICAgICAgaWYgKHRoaXMubG9nKSB7XG4gICAgICAgIHRoaXMubG9nKFwibmV3RWxcIiwgbmV3RWwsIFwib2xkRWxcIiwgb2xkRWwpXG4gICAgICB9XG4gICAgICBpZiAoc3dpdGNoZXNbc2VsZWN0b3JdKSB7XG4gICAgICAgIHN3aXRjaGVzW3NlbGVjdG9yXS5iaW5kKHRoaXMpKG9sZEVsLCBuZXdFbCwgb3B0aW9ucywgc3dpdGNoZXNPcHRpb25zW3NlbGVjdG9yXSlcbiAgICAgIH1cbiAgICAgIGVsc2Uge1xuICAgICAgICBkZWZhdWx0U3dpdGNoZXMub3V0ZXJIVE1MLmJpbmQodGhpcykob2xkRWwsIG5ld0VsLCBvcHRpb25zKVxuICAgICAgfVxuICAgIH0sIHRoaXMpXG4gIH0sIHRoaXMpXG59XG4iLCJ2YXIgb24gPSByZXF1aXJlKFwiLi9ldmVudHMvb24uanNcIilcbi8vIHZhciBvZmYgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL29uLmpzXCIpXG4vLyB2YXIgdHJpZ2dlciA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvdHJpZ2dlci5qc1wiKVxuXG5cbm1vZHVsZS5leHBvcnRzID0ge1xuICBvdXRlckhUTUw6IGZ1bmN0aW9uKG9sZEVsLCBuZXdFbCkge1xuICAgIG9sZEVsLm91dGVySFRNTCA9IG5ld0VsLm91dGVySFRNTFxuICAgIHRoaXMub25Td2l0Y2goKVxuICB9LFxuXG4gIGlubmVySFRNTDogZnVuY3Rpb24ob2xkRWwsIG5ld0VsKSB7XG4gICAgb2xkRWwuaW5uZXJIVE1MID0gbmV3RWwuaW5uZXJIVE1MXG4gICAgb2xkRWwuY2xhc3NOYW1lID0gbmV3RWwuY2xhc3NOYW1lXG4gICAgdGhpcy5vblN3aXRjaCgpXG4gIH0sXG5cbiAgc2lkZUJ5U2lkZTogZnVuY3Rpb24ob2xkRWwsIG5ld0VsLCBvcHRpb25zLCBzd2l0Y2hPcHRpb25zKSB7XG4gICAgdmFyIGZvckVhY2ggPSBBcnJheS5wcm90b3R5cGUuZm9yRWFjaFxuICAgIHZhciBlbHNUb1JlbW92ZSA9IFtdXG4gICAgdmFyIGVsc1RvQWRkID0gW11cbiAgICB2YXIgZnJhZ1RvQXBwZW5kID0gZG9jdW1lbnQuY3JlYXRlRG9jdW1lbnRGcmFnbWVudCgpXG4gICAgLy8gaGVpZ2h0IHRyYW5zaXRpb24gYXJlIHNoaXR0eSBvbiBzYWZhcmlcbiAgICAvLyBzbyBjb21tZW50ZWQgZm9yIG5vdyAodW50aWwgSSBmb3VuZCBzb21ldGhpbmcgPylcbiAgICAvLyB2YXIgcmVsZXZhbnRIZWlnaHQgPSAwXG4gICAgdmFyIGFuaW1hdGlvbkV2ZW50TmFtZXMgPSBcImFuaW1hdGlvbmVuZCB3ZWJraXRBbmltYXRpb25FbmQgTVNBbmltYXRpb25FbmQgb2FuaW1hdGlvbmVuZFwiXG4gICAgdmFyIGFuaW1hdGVkRWxzTnVtYmVyID0gMFxuICAgIHZhciBzZXh5QW5pbWF0aW9uRW5kID0gZnVuY3Rpb24oZSkge1xuICAgICAgICAgIGlmIChlLnRhcmdldCAhPSBlLmN1cnJlbnRUYXJnZXQpIHtcbiAgICAgICAgICAgIC8vIGVuZCB0cmlnZ2VyZWQgYnkgYW4gYW5pbWF0aW9uIG9uIGEgY2hpbGRcbiAgICAgICAgICAgIHJldHVyblxuICAgICAgICAgIH1cblxuICAgICAgICAgIGFuaW1hdGVkRWxzTnVtYmVyLS1cbiAgICAgICAgICBpZiAoYW5pbWF0ZWRFbHNOdW1iZXIgPD0gMCAmJiBlbHNUb1JlbW92ZSkge1xuICAgICAgICAgICAgZWxzVG9SZW1vdmUuZm9yRWFjaChmdW5jdGlvbihlbCkge1xuICAgICAgICAgICAgICAvLyBicm93c2luZyBxdWlja2x5IGNhbiBtYWtlIHRoZSBlbFxuICAgICAgICAgICAgICAvLyBhbHJlYWR5IHJlbW92ZWQgYnkgbGFzdCBwYWdlIHVwZGF0ZSA/XG4gICAgICAgICAgICAgIGlmIChlbC5wYXJlbnROb2RlKSB7XG4gICAgICAgICAgICAgICAgZWwucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChlbClcbiAgICAgICAgICAgICAgfVxuICAgICAgICAgICAgfSlcblxuICAgICAgICAgICAgZWxzVG9BZGQuZm9yRWFjaChmdW5jdGlvbihlbCkge1xuICAgICAgICAgICAgICBlbC5jbGFzc05hbWUgPSBlbC5jbGFzc05hbWUucmVwbGFjZShlbC5nZXRBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKSwgXCJcIilcbiAgICAgICAgICAgICAgZWwucmVtb3ZlQXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIilcbiAgICAgICAgICAgICAgLy8gUGpheC5vZmYoZWwsIGFuaW1hdGlvbkV2ZW50TmFtZXMsIHNleHlBbmltYXRpb25FbmQsIHRydWUpXG4gICAgICAgICAgICB9KVxuXG4gICAgICAgICAgICBlbHNUb0FkZCA9IG51bGwgLy8gZnJlZSBtZW1vcnlcbiAgICAgICAgICAgIGVsc1RvUmVtb3ZlID0gbnVsbCAvLyBmcmVlIG1lbW9yeVxuXG4gICAgICAgICAgICAvLyBhc3N1bWUgdGhlIGhlaWdodCBpcyBub3cgdXNlbGVzcyAoYXZvaWQgYnVnIHNpbmNlIHRoZXJlIGlzIG92ZXJmbG93IGhpZGRlbiBvbiB0aGUgcGFyZW50KVxuICAgICAgICAgICAgLy8gb2xkRWwuc3R5bGUuaGVpZ2h0ID0gXCJhdXRvXCJcblxuICAgICAgICAgICAgLy8gdGhpcyBpcyB0byB0cmlnZ2VyIHNvbWUgcmVwYWludCAoZXhhbXBsZTogcGljdHVyZWZpbGwpXG4gICAgICAgICAgICB0aGlzLm9uU3dpdGNoKClcbiAgICAgICAgICAgIC8vIFBqYXgudHJpZ2dlcih3aW5kb3csIFwic2Nyb2xsXCIpXG4gICAgICAgICAgfVxuICAgICAgICB9LmJpbmQodGhpcylcblxuICAgIC8vIEZvcmNlIGhlaWdodCB0byBiZSBhYmxlIHRvIHRyaWdnZXIgY3NzIGFuaW1hdGlvblxuICAgIC8vIGhlcmUgd2UgZ2V0IHRoZSByZWxldmFudCBoZWlnaHRcbiAgICAvLyBvbGRFbC5wYXJlbnROb2RlLmFwcGVuZENoaWxkKG5ld0VsKVxuICAgIC8vIHJlbGV2YW50SGVpZ2h0ID0gbmV3RWwuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0XG4gICAgLy8gb2xkRWwucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChuZXdFbClcbiAgICAvLyBvbGRFbC5zdHlsZS5oZWlnaHQgPSBvbGRFbC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHQgKyBcInB4XCJcblxuICAgIHN3aXRjaE9wdGlvbnMgPSBzd2l0Y2hPcHRpb25zIHx8IHt9XG5cbiAgICBmb3JFYWNoLmNhbGwob2xkRWwuY2hpbGROb2RlcywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIGVsc1RvUmVtb3ZlLnB1c2goZWwpXG4gICAgICBpZiAoZWwuY2xhc3NMaXN0ICYmICFlbC5jbGFzc0xpc3QuY29udGFpbnMoXCJqcy1QamF4LXJlbW92ZVwiKSkge1xuICAgICAgICAvLyBmb3IgZmFzdCBzd2l0Y2gsIGNsZWFuIGVsZW1lbnQgdGhhdCBqdXN0IGhhdmUgYmVlbiBhZGRlZCwgJiBub3QgY2xlYW5lZCB5ZXQuXG4gICAgICAgIGlmIChlbC5oYXNBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKSkge1xuICAgICAgICAgIGVsLmNsYXNzTmFtZSA9IGVsLmNsYXNzTmFtZS5yZXBsYWNlKGVsLmdldEF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpLCBcIlwiKVxuICAgICAgICAgIGVsLnJlbW92ZUF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpXG4gICAgICAgIH1cbiAgICAgICAgZWwuY2xhc3NMaXN0LmFkZChcImpzLVBqYXgtcmVtb3ZlXCIpXG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcyAmJiBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5yZW1vdmVFbGVtZW50KSB7XG4gICAgICAgICAgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MucmVtb3ZlRWxlbWVudChlbClcbiAgICAgICAgfVxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzKSB7XG4gICAgICAgICAgZWwuY2xhc3NOYW1lICs9IFwiIFwiICsgc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLnJlbW92ZSArIFwiIFwiICsgKG9wdGlvbnMuYmFja3dhcmQgPyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuYmFja3dhcmQgOiBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuZm9yd2FyZClcbiAgICAgICAgfVxuICAgICAgICBhbmltYXRlZEVsc051bWJlcisrXG4gICAgICAgIG9uKGVsLCBhbmltYXRpb25FdmVudE5hbWVzLCBzZXh5QW5pbWF0aW9uRW5kLCB0cnVlKVxuICAgICAgfVxuICAgIH0pXG5cbiAgICBmb3JFYWNoLmNhbGwobmV3RWwuY2hpbGROb2RlcywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIGlmIChlbC5jbGFzc0xpc3QpIHtcbiAgICAgICAgdmFyIGFkZENsYXNzZXMgPSBcIlwiXG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMpIHtcbiAgICAgICAgICBhZGRDbGFzc2VzID0gXCIganMtUGpheC1hZGQgXCIgKyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuYWRkICsgXCIgXCIgKyAob3B0aW9ucy5iYWNrd2FyZCA/IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5mb3J3YXJkIDogc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmJhY2t3YXJkKVxuICAgICAgICB9XG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcyAmJiBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5hZGRFbGVtZW50KSB7XG4gICAgICAgICAgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MuYWRkRWxlbWVudChlbClcbiAgICAgICAgfVxuICAgICAgICBlbC5jbGFzc05hbWUgKz0gYWRkQ2xhc3Nlc1xuICAgICAgICBlbC5zZXRBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiLCBhZGRDbGFzc2VzKVxuICAgICAgICBlbHNUb0FkZC5wdXNoKGVsKVxuICAgICAgICBmcmFnVG9BcHBlbmQuYXBwZW5kQ2hpbGQoZWwpXG4gICAgICAgIGFuaW1hdGVkRWxzTnVtYmVyKytcbiAgICAgICAgb24oZWwsIGFuaW1hdGlvbkV2ZW50TmFtZXMsIHNleHlBbmltYXRpb25FbmQsIHRydWUpXG4gICAgICB9XG4gICAgfSlcblxuICAgIC8vIHBhc3MgYWxsIGNsYXNzTmFtZSBvZiB0aGUgcGFyZW50XG4gICAgb2xkRWwuY2xhc3NOYW1lID0gbmV3RWwuY2xhc3NOYW1lXG4gICAgb2xkRWwuYXBwZW5kQ2hpbGQoZnJhZ1RvQXBwZW5kKVxuXG4gICAgLy8gb2xkRWwuc3R5bGUuaGVpZ2h0ID0gcmVsZXZhbnRIZWlnaHQgKyBcInB4XCJcbiAgfVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSAoZnVuY3Rpb24oKSB7XG4gIHZhciBjb3VudGVyID0gMFxuICByZXR1cm4gZnVuY3Rpb24oKSB7XG4gICAgdmFyIGlkID0gKFwicGpheFwiICsgKG5ldyBEYXRlKCkuZ2V0VGltZSgpKSkgKyBcIl9cIiArIGNvdW50ZXJcbiAgICBjb3VudGVyKytcbiAgICByZXR1cm4gaWRcbiAgfVxufSkoKVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9mb3JlYWNoLWVsc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsZW1lbnRzLCBvbGRFbGVtZW50cykge1xuICAgdGhpcy5sb2coXCJzdHlsZWhlZXRzIG9sZCBlbGVtZW50c1wiLCBvbGRFbGVtZW50cyk7XG4gICB0aGlzLmxvZyhcInN0eWxlaGVldHMgbmV3IGVsZW1lbnRzXCIsIGVsZW1lbnRzKTtcbiAgdmFyIHRvQXJyYXkgPSBmdW5jdGlvbihlbnVtZXJhYmxlKXtcbiAgICAgIHZhciBhcnIgPSBbXTtcbiAgICAgIGZvcih2YXIgaSA9IGVudW1lcmFibGUubGVuZ3RoOyBpLS07IGFyci51bnNoaWZ0KGVudW1lcmFibGVbaV0pKTtcbiAgICAgIHJldHVybiBhcnI7XG4gIH07XG4gIGZvckVhY2hFbHMoZWxlbWVudHMsIGZ1bmN0aW9uKG5ld0VsLCBpKSB7XG4gICAgdmFyIG9sZEVsZW1lbnRzQXJyYXkgPSB0b0FycmF5KG9sZEVsZW1lbnRzKTtcbiAgICB2YXIgcmVzZW1ibGluZ09sZCA9IG9sZEVsZW1lbnRzQXJyYXkucmVkdWNlKGZ1bmN0aW9uKGFjYywgb2xkRWwpe1xuICAgICAgYWNjID0gKChvbGRFbC5ocmVmID09PSBuZXdFbC5ocmVmKSA/IG9sZEVsIDogYWNjKTtcbiAgICAgIHJldHVybiBhY2M7XG4gICAgfSwgbnVsbCk7XG5cbiAgICBpZihyZXNlbWJsaW5nT2xkICE9PSBudWxsKXtcbiAgICAgIGlmICh0aGlzLmxvZykge1xuICAgICAgICB0aGlzLmxvZyhcIm9sZCBzdHlsZXNoZWV0IGZvdW5kIG5vdCByZXNldHRpbmdcIik7XG4gICAgICB9XG4gICAgfSBlbHNlIHtcbiAgICAgIGlmICh0aGlzLmxvZykge1xuICAgICAgICB0aGlzLmxvZyhcIm5ldyBzdHlsZXNoZWV0ID0+IGFkZCB0byBoZWFkXCIpO1xuICAgICAgfVxuICAgICAgdmFyIGhlYWQgPSBkb2N1bWVudC5nZXRFbGVtZW50c0J5VGFnTmFtZSggJ2hlYWQnIClbMF0sXG4gICAgICAgbGluayA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoICdsaW5rJyApO1xuICAgICAgICBsaW5rLnNldEF0dHJpYnV0ZSggJ2hyZWYnLCBuZXdFbC5ocmVmICk7XG4gICAgICAgIGxpbmsuc2V0QXR0cmlidXRlKCAncmVsJywgJ3N0eWxlc2hlZXQnICk7XG4gICAgICAgIGxpbmsuc2V0QXR0cmlidXRlKCAndHlwZScsICd0ZXh0L2NzcycgKTtcbiAgICAgICAgaGVhZC5hcHBlbmRDaGlsZChsaW5rKTtcbiAgICB9XG4gIH0sIHRoaXMpO1xuXG59XG4iXX0=
