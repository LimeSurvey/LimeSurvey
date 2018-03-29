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

  parseDOMtoUnload: require("./lib/proto/parse-dom-unload.js"),

  refresh: require("./lib/proto/refresh.js"),

  reload: require("./lib/reload.js"),

  attachLink: require("./lib/proto/attach-link.js"),

  attachForm: require("./lib/proto/attach-form.js"),

  unattachLink: require("./lib/proto/unattach-link.js"),

  unattachForm: require("./lib/proto/unattach-form.js"),

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
    var collectForScriptcomplete = [
      (Promise.resolve("basic resolve"))
    ];

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

        collectForScriptcomplete.push.apply(collectForScriptcomplete, executeScripts.call(this, el));

      }, this);

    },this);
    // }
    // catch(e) {
    //   if (this.options.debug) {
    //     this.log("Pjax switch fail: ", e)
    //   }
    //   this.switchFallback(tmpEl, document)
    // }
    this.log("waiting for scriptcomplete",collectForScriptcomplete);

    //Fallback! If something can't be loaded or is not loaded correctly -> just force eventing in error
    var timeOutScriptEvent = null;
    timeOutScriptEvent = window.setTimeout( function(){
      trigger(document,"pjax:scriptcomplete pjax:scripttimeout", options)
      timeOutScriptEvent = null;
    }, this.options.scriptloadtimeout);

    Promise.all(collectForScriptcomplete).then(
      //resolved
      function(){
        if(timeOutScriptEvent !== null ){
          window.clearTimeout(timeOutScriptEvent);
          trigger(document,"pjax:scriptcomplete pjax:scriptsuccess", options)
        }
      },
      function(){
        if(timeOutScriptEvent !== null ){
          window.clearTimeout(timeOutScriptEvent);
          trigger(document,"pjax:scriptcomplete pjax:scripterror", options)
        }
      }
    );


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
          if (console && this.options.logObject.error) {
            this.options.logObject.error("Pjax switch fail: ", e)
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

},{"./lib/clone.js":2,"./lib/events/on.js":5,"./lib/events/trigger.js":6,"./lib/execute-scripts.js":7,"./lib/foreach-els.js":8,"./lib/foreach-selectors.js":9,"./lib/is-supported.js":10,"./lib/proto/attach-form.js":12,"./lib/proto/attach-link.js":13,"./lib/proto/get-elements.js":14,"./lib/proto/log.js":15,"./lib/proto/parse-dom-unload.js":16,"./lib/proto/parse-dom.js":17,"./lib/proto/parse-options.js":20,"./lib/proto/refresh.js":21,"./lib/proto/unattach-form.js":22,"./lib/proto/unattach-link.js":23,"./lib/reload.js":24,"./lib/request.js":25,"./lib/switches-selectors.js":26,"./lib/uniqueid.js":28,"./lib/update-stylesheets.js":29}],2:[function(require,module,exports){
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
    if (console && this.options.logObject.log) {
      this.options.logObject.log("Script contains document.write. Can’t be executed correctly. Code skipped ", el)
    }
    return false
  }

  promise = new Promise( function(resolve, reject){

    script.type = "text/javascript"
    if (src != "") {
      script.src = src;
      script.addEventListener('load', function(){resolve(src);} );
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
      resolve('text-node');
    }
  });

  this.log('ParentElement => ', parent );

  // execute
  parent.appendChild(script);
  parent.removeChild(script)
  // avoid pollution only in head or body tags
  // of if the setting removeScriptsAfterParsing is active
  if( (["head","body"].indexOf( parent.tagName.toLowerCase()) > 0) || (this.options.removeScriptsAfterParsing === true) ) {
  }

  return promise;
}

},{}],4:[function(require,module,exports){
var forEachEls = require("../foreach-els")

module.exports = function(els, events, listener, useCapture) {
  events = (typeof events === "string" ? events.split(" ") : events)

  events.forEach(function(e) {
    forEachEls(els, function(el) {
      el.removeEventListener(e, listener, useCapture)
    })
  })
}

},{"../foreach-els":8}],5:[function(require,module,exports){
var forEachEls = require("../foreach-els")

module.exports = function(els, events, listener, useCapture) {
  events = (typeof events === "string" ? events.split(" ") : events)

  events.forEach(function(e) {
    forEachEls(els, function(el) {
      el.addEventListener(e, listener, useCapture)
    })
  })
}

},{"../foreach-els":8}],6:[function(require,module,exports){
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

},{"../foreach-els":8}],7:[function(require,module,exports){
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

  return loadingScripts;
}

},{"./eval-script":3,"./foreach-els":8}],8:[function(require,module,exports){
/* global HTMLCollection: true */

module.exports = function(els, fn, context) {
  if (els instanceof HTMLCollection || els instanceof NodeList || els instanceof Array) {
    return Array.prototype.forEach.call(els, fn, context)
  }
  // assume simple dom element
  return fn.call(context, els)
}

},{}],9:[function(require,module,exports){
var forEachEls = require("./foreach-els")

module.exports = function(selectors, cb, context, DOMcontext) {
  DOMcontext = DOMcontext || document
  selectors.forEach(function(selector) {
    forEachEls(DOMcontext.querySelectorAll(selector), cb, context)
  })
}

},{"./foreach-els":8}],10:[function(require,module,exports){
module.exports = function() {
  // Borrowed wholesale from https://github.com/defunkt/jquery-pjax
  return window.history &&
    window.history.pushState &&
    window.history.replaceState &&
    // pushState isn’t reliable on iOS until 5.
    !navigator.userAgent.match(/((iPod|iPhone|iPad).+\bOS\s+[1-4]\D|WebApps\/.+CFNetwork)/)
}

},{}],11:[function(require,module,exports){
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

},{}],12:[function(require,module,exports){
require("../polyfills/Function.prototype.bind")

var on = require("../events/on")
var clone = require("../clone")

var attrClick = "data-pjax-submit-state"

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
}

},{"../clone":2,"../events/on":5,"../polyfills/Function.prototype.bind":11}],13:[function(require,module,exports){
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

},{"../clone":2,"../events/on":5,"../polyfills/Function.prototype.bind":11}],14:[function(require,module,exports){
module.exports = function(el) {
  return el.querySelectorAll(this.options.elements)
}

},{}],15:[function(require,module,exports){
module.exports = function() {
  if ((this.options.debug && this.options.logObject)) {
    if (typeof this.options.logObject.log === "function") {
      this.options.logObject.log.apply(this.options.logObject, ['PJAX ->',arguments]);
    }
    // ie is weird
    else if (this.options.logObject.log) {
      this.options.logObject.log(['PJAX ->',arguments]);
    }
  }
}

},{}],16:[function(require,module,exports){
var forEachEls = require("../foreach-els")

var parseElementUnload = require("./parse-element-unload")

module.exports = function(el) {
  forEachEls(this.getElements(el), parseElementUnload, this)
}

},{"../foreach-els":8,"./parse-element-unload":18}],17:[function(require,module,exports){
var forEachEls = require("../foreach-els")

var parseElement = require("./parse-element")

module.exports = function(el) {
  forEachEls(this.getElements(el), parseElement, this)
}

},{"../foreach-els":8,"./parse-element":19}],18:[function(require,module,exports){
module.exports = function(el) {
  switch (el.tagName.toLowerCase()) {
  case "a":
    // only attach link if el does not already have link attached
    if (!el.hasAttribute('data-pjax-click-state')) {
      this.unattachLink(el)
    }
    break

    case "form":
      // only attach link if el does not already have link attached
      if (!el.hasAttribute('data-pjax-click-state')) {
        this.unattachForm(el)
      }
    break

  default:
    throw "Pjax can only be applied on <a> or <form> submit"
  }
}

},{}],19:[function(require,module,exports){
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

},{}],20:[function(require,module,exports){
/* global _gaq: true, ga: true */

module.exports = function(options){
  this.options = options
  this.options.elements = this.options.elements || "a[href], form[action]",
  this.options.reRenderCSS = this.options.reRenderCSS || false,
  this.options.forceRedirectOnFail = this.options.forceRedirectOnFail || false,
  this.options.scriptloadtimeout = this.options.scriptloadtimeout || 1000,
  this.options.mainScriptElement = this.options.mainScriptElement || "head"
  this.options.removeScriptsAfterParsing = this.options.removeScriptsAfterParsing || true
  this.options.logObject = this.options.logObject || console
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

},{}],21:[function(require,module,exports){
module.exports = function(el) {
  this.parseDOM(el || document)
}

},{}],22:[function(require,module,exports){
require("../polyfills/Function.prototype.bind")

var off = require("../events/off")
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

  off(el, "submit", function(event) {
    if (isDefaultPrevented(event)) {
      return
    }

    formAction.call(that, el, event)
  })

  off(el, "keyup", function(event) {
    if (isDefaultPrevented(event)) {
      return
    }


    if (event.keyCode == 13) {
      formAction.call(that, el, event)
    }
  }.bind(this))
}

},{"../clone":2,"../events/off":4,"../polyfills/Function.prototype.bind":11}],23:[function(require,module,exports){
require("../polyfills/Function.prototype.bind")

var off = require("../events/off")
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

  off(el, "click", function(event) {
    if (isDefaultPrevented(event)) {
      return
    }

    linkAction.call(that, el, event)
  })

  off(el, "keyup", function(event) {
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

},{"../clone":2,"../events/off":4,"../polyfills/Function.prototype.bind":11}],24:[function(require,module,exports){
module.exports = function() {
  window.location.reload()
}

},{}],25:[function(require,module,exports){
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

},{}],26:[function(require,module,exports){
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

},{"./foreach-els":8,"./switches":27}],27:[function(require,module,exports){
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

},{"./events/on.js":5}],28:[function(require,module,exports){
module.exports = (function() {
  var counter = 0
  return function() {
    var id = ("pjax" + (new Date().getTime())) + "_" + counter
    counter++
    return id
  }
})()

},{}],29:[function(require,module,exports){
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

},{"./foreach-els":8}]},{},[1])(1)
});
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi4uLy4uL25vZGUvbGliL25vZGVfbW9kdWxlcy9icm93c2VyaWZ5L25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJpbmRleC5qcyIsImxpYi9jbG9uZS5qcyIsImxpYi9ldmFsLXNjcmlwdC5qcyIsImxpYi9ldmVudHMvb2ZmLmpzIiwibGliL2V2ZW50cy9vbi5qcyIsImxpYi9ldmVudHMvdHJpZ2dlci5qcyIsImxpYi9leGVjdXRlLXNjcmlwdHMuanMiLCJsaWIvZm9yZWFjaC1lbHMuanMiLCJsaWIvZm9yZWFjaC1zZWxlY3RvcnMuanMiLCJsaWIvaXMtc3VwcG9ydGVkLmpzIiwibGliL3BvbHlmaWxscy9GdW5jdGlvbi5wcm90b3R5cGUuYmluZC5qcyIsImxpYi9wcm90by9hdHRhY2gtZm9ybS5qcyIsImxpYi9wcm90by9hdHRhY2gtbGluay5qcyIsImxpYi9wcm90by9nZXQtZWxlbWVudHMuanMiLCJsaWIvcHJvdG8vbG9nLmpzIiwibGliL3Byb3RvL3BhcnNlLWRvbS11bmxvYWQuanMiLCJsaWIvcHJvdG8vcGFyc2UtZG9tLmpzIiwibGliL3Byb3RvL3BhcnNlLWVsZW1lbnQtdW5sb2FkLmpzIiwibGliL3Byb3RvL3BhcnNlLWVsZW1lbnQuanMiLCJsaWIvcHJvdG8vcGFyc2Utb3B0aW9ucy5qcyIsImxpYi9wcm90by9yZWZyZXNoLmpzIiwibGliL3Byb3RvL3VuYXR0YWNoLWZvcm0uanMiLCJsaWIvcHJvdG8vdW5hdHRhY2gtbGluay5qcyIsImxpYi9yZWxvYWQuanMiLCJsaWIvcmVxdWVzdC5qcyIsImxpYi9zd2l0Y2hlcy1zZWxlY3RvcnMuanMiLCJsaWIvc3dpdGNoZXMuanMiLCJsaWIvdW5pcXVlaWQuanMiLCJsaWIvdXBkYXRlLXN0eWxlc2hlZXRzLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDblNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1pBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNsREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNYQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQy9CQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMzQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDVEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNSQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN6RkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3pGQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDWEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNQQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1BBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BCQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDNUNBO0FBQ0E7QUFDQTtBQUNBOztBQ0hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN6RkE7QUFDQTtBQUNBO0FBQ0E7O0FDSEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25DQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDbkhBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNSQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0EiLCJmaWxlIjoiZ2VuZXJhdGVkLmpzIiwic291cmNlUm9vdCI6IiIsInNvdXJjZXNDb250ZW50IjpbIihmdW5jdGlvbiBlKHQsbixyKXtmdW5jdGlvbiBzKG8sdSl7aWYoIW5bb10pe2lmKCF0W29dKXt2YXIgYT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2lmKCF1JiZhKXJldHVybiBhKG8sITApO2lmKGkpcmV0dXJuIGkobywhMCk7dmFyIGY9bmV3IEVycm9yKFwiQ2Fubm90IGZpbmQgbW9kdWxlICdcIitvK1wiJ1wiKTt0aHJvdyBmLmNvZGU9XCJNT0RVTEVfTk9UX0ZPVU5EXCIsZn12YXIgbD1uW29dPXtleHBvcnRzOnt9fTt0W29dWzBdLmNhbGwobC5leHBvcnRzLGZ1bmN0aW9uKGUpe3ZhciBuPXRbb11bMV1bZV07cmV0dXJuIHMobj9uOmUpfSxsLGwuZXhwb3J0cyxlLHQsbixyKX1yZXR1cm4gbltvXS5leHBvcnRzfXZhciBpPXR5cGVvZiByZXF1aXJlPT1cImZ1bmN0aW9uXCImJnJlcXVpcmU7Zm9yKHZhciBvPTA7bzxyLmxlbmd0aDtvKyspcyhyW29dKTtyZXR1cm4gc30pIiwidmFyIGNsb25lID0gcmVxdWlyZSgnLi9saWIvY2xvbmUuanMnKVxudmFyIGV4ZWN1dGVTY3JpcHRzID0gcmVxdWlyZSgnLi9saWIvZXhlY3V0ZS1zY3JpcHRzLmpzJylcbnZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vbGliL2ZvcmVhY2gtZWxzLmpzXCIpXG52YXIgbmV3VWlkID0gcmVxdWlyZShcIi4vbGliL3VuaXF1ZWlkLmpzXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvb24uanNcIilcbi8vIHZhciBvZmYgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL29uLmpzXCIpXG52YXIgdHJpZ2dlciA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvdHJpZ2dlci5qc1wiKVxuXG5cbnZhciBQamF4ID0gZnVuY3Rpb24ob3B0aW9ucykge1xuICAgIHRoaXMuZmlyc3RydW4gPSB0cnVlXG5cbiAgICB2YXIgcGFyc2VPcHRpb25zID0gcmVxdWlyZShcIi4vbGliL3Byb3RvL3BhcnNlLW9wdGlvbnMuanNcIik7XG4gICAgcGFyc2VPcHRpb25zLmFwcGx5KHRoaXMsW29wdGlvbnNdKVxuICAgIHRoaXMubG9nKFwiUGpheCBvcHRpb25zXCIsIHRoaXMub3B0aW9ucylcblxuICAgIHRoaXMubWF4VWlkID0gdGhpcy5sYXN0VWlkID0gbmV3VWlkKClcblxuICAgIHRoaXMucGFyc2VET00oZG9jdW1lbnQpXG5cbiAgICBvbih3aW5kb3csIFwicG9wc3RhdGVcIiwgZnVuY3Rpb24oc3QpIHtcbiAgICAgIGlmIChzdC5zdGF0ZSkge1xuICAgICAgICB2YXIgb3B0ID0gY2xvbmUodGhpcy5vcHRpb25zKVxuICAgICAgICBvcHQudXJsID0gc3Quc3RhdGUudXJsXG4gICAgICAgIG9wdC50aXRsZSA9IHN0LnN0YXRlLnRpdGxlXG4gICAgICAgIG9wdC5oaXN0b3J5ID0gZmFsc2VcbiAgICAgICAgb3B0LnJlcXVlc3RPcHRpb25zID0ge307XG4gICAgICAgIGlmIChzdC5zdGF0ZS51aWQgPCB0aGlzLmxhc3RVaWQpIHtcbiAgICAgICAgICBvcHQuYmFja3dhcmQgPSB0cnVlXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgb3B0LmZvcndhcmQgPSB0cnVlXG4gICAgICAgIH1cbiAgICAgICAgdGhpcy5sYXN0VWlkID0gc3Quc3RhdGUudWlkXG5cbiAgICAgICAgLy8gQHRvZG8gaW1wbGVtZW50IGhpc3RvcnkgY2FjaGUgaGVyZSwgYmFzZWQgb24gdWlkXG4gICAgICAgIHRoaXMubG9hZFVybChzdC5zdGF0ZS51cmwsIG9wdClcbiAgICAgIH1cbiAgICB9LmJpbmQodGhpcykpO1xuXG4gICAgcmV0dXJuIHRoaXM7XG4gIH1cblxuUGpheC5wcm90b3R5cGUgPSB7XG4gIGxvZzogcmVxdWlyZShcIi4vbGliL3Byb3RvL2xvZy5qc1wiKSxcblxuICBnZXRFbGVtZW50czogcmVxdWlyZShcIi4vbGliL3Byb3RvL2dldC1lbGVtZW50cy5qc1wiKSxcblxuICBwYXJzZURPTTogcmVxdWlyZShcIi4vbGliL3Byb3RvL3BhcnNlLWRvbS5qc1wiKSxcblxuICBwYXJzZURPTXRvVW5sb2FkOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vcGFyc2UtZG9tLXVubG9hZC5qc1wiKSxcblxuICByZWZyZXNoOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vcmVmcmVzaC5qc1wiKSxcblxuICByZWxvYWQ6IHJlcXVpcmUoXCIuL2xpYi9yZWxvYWQuanNcIiksXG5cbiAgYXR0YWNoTGluazogcmVxdWlyZShcIi4vbGliL3Byb3RvL2F0dGFjaC1saW5rLmpzXCIpLFxuXG4gIGF0dGFjaEZvcm06IHJlcXVpcmUoXCIuL2xpYi9wcm90by9hdHRhY2gtZm9ybS5qc1wiKSxcblxuICB1bmF0dGFjaExpbms6IHJlcXVpcmUoXCIuL2xpYi9wcm90by91bmF0dGFjaC1saW5rLmpzXCIpLFxuXG4gIHVuYXR0YWNoRm9ybTogcmVxdWlyZShcIi4vbGliL3Byb3RvL3VuYXR0YWNoLWZvcm0uanNcIiksXG5cbiAgdXBkYXRlU3R5bGVzaGVldHM6IHJlcXVpcmUoXCIuL2xpYi91cGRhdGUtc3R5bGVzaGVldHMuanNcIiksXG5cbiAgZm9yRWFjaFNlbGVjdG9yczogZnVuY3Rpb24oY2IsIGNvbnRleHQsIERPTWNvbnRleHQpIHtcbiAgICByZXR1cm4gcmVxdWlyZShcIi4vbGliL2ZvcmVhY2gtc2VsZWN0b3JzLmpzXCIpLmJpbmQodGhpcykodGhpcy5vcHRpb25zLnNlbGVjdG9ycywgY2IsIGNvbnRleHQsIERPTWNvbnRleHQpXG4gIH0sXG5cbiAgc3dpdGNoU2VsZWN0b3JzOiBmdW5jdGlvbihzZWxlY3RvcnMsIGZyb21FbCwgdG9FbCwgb3B0aW9ucykge1xuICAgIHJldHVybiByZXF1aXJlKFwiLi9saWIvc3dpdGNoZXMtc2VsZWN0b3JzLmpzXCIpLmJpbmQodGhpcykodGhpcy5vcHRpb25zLnN3aXRjaGVzLCB0aGlzLm9wdGlvbnMuc3dpdGNoZXNPcHRpb25zLCBzZWxlY3RvcnMsIGZyb21FbCwgdG9FbCwgb3B0aW9ucylcbiAgfSxcblxuICAvLyB0b28gbXVjaCBwcm9ibGVtIHdpdGggdGhlIGNvZGUgYmVsb3dcbiAgLy8gKyBpdOKAmXMgdG9vIGRhbmdlcm91c1xuLy8gICBzd2l0Y2hGYWxsYmFjazogZnVuY3Rpb24oZnJvbUVsLCB0b0VsKSB7XG4vLyAgICAgdGhpcy5zd2l0Y2hTZWxlY3RvcnMoW1wiaGVhZFwiLCBcImJvZHlcIl0sIGZyb21FbCwgdG9FbClcbi8vICAgICAvLyBleGVjdXRlIHNjcmlwdCB3aGVuIERPTSBpcyBsaWtlIGl0IHNob3VsZCBiZVxuLy8gICAgIFBqYXguZXhlY3V0ZVNjcmlwdHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcImhlYWRcIikpXG4vLyAgICAgUGpheC5leGVjdXRlU2NyaXB0cyhkb2N1bWVudC5xdWVyeVNlbGVjdG9yKFwiYm9keVwiKSlcbi8vICAgfVxuXG4gIGxhdGVzdENoYW5jZTogZnVuY3Rpb24oaHJlZikge1xuICAgIHdpbmRvdy5sb2NhdGlvbiA9IGhyZWZcbiAgfSxcblxuICBvblN3aXRjaDogZnVuY3Rpb24oKSB7XG4gICAgdHJpZ2dlcih3aW5kb3csIFwicmVzaXplIHNjcm9sbFwiKVxuICB9LFxuXG4gIGxvYWRDb250ZW50OiBmdW5jdGlvbihodG1sLCBvcHRpb25zKSB7XG4gICAgdmFyIHRtcEVsID0gZG9jdW1lbnQuaW1wbGVtZW50YXRpb24uY3JlYXRlSFRNTERvY3VtZW50KFwicGpheFwiKVxuICAgIHZhciBjb2xsZWN0Rm9yU2NyaXB0Y29tcGxldGUgPSBbXG4gICAgICAoUHJvbWlzZS5yZXNvbHZlKFwiYmFzaWMgcmVzb2x2ZVwiKSlcbiAgICBdO1xuXG4gICAgLy8gcGFyc2UgSFRNTCBhdHRyaWJ1dGVzIHRvIGNvcHkgdGhlbVxuICAgIC8vIHNpbmNlIHdlIGFyZSBmb3JjZWQgdG8gdXNlIGRvY3VtZW50RWxlbWVudC5pbm5lckhUTUwgKG91dGVySFRNTCBjYW4ndCBiZSB1c2VkIGZvciA8aHRtbD4pXG4gICAgdmFyIGh0bWxSZWdleCA9IC88aHRtbFtePl0rPi9naVxuICAgIHZhciBodG1sQXR0cmlic1JlZ2V4ID0gL1xccz9bYS16Ol0rKD86XFw9KD86XFwnfFxcXCIpW15cXCdcXFwiPl0rKD86XFwnfFxcXCIpKSovZ2lcbiAgICB2YXIgbWF0Y2hlcyA9IGh0bWwubWF0Y2goaHRtbFJlZ2V4KVxuICAgIGlmIChtYXRjaGVzICYmIG1hdGNoZXMubGVuZ3RoKSB7XG4gICAgICBtYXRjaGVzID0gbWF0Y2hlc1swXS5tYXRjaChodG1sQXR0cmlic1JlZ2V4KVxuICAgICAgaWYgKG1hdGNoZXMubGVuZ3RoKSB7XG4gICAgICAgIG1hdGNoZXMuc2hpZnQoKVxuICAgICAgICBtYXRjaGVzLmZvckVhY2goZnVuY3Rpb24oaHRtbEF0dHJpYikge1xuICAgICAgICAgIHZhciBhdHRyID0gaHRtbEF0dHJpYi50cmltKCkuc3BsaXQoXCI9XCIpXG4gICAgICAgICAgaWYgKGF0dHIubGVuZ3RoID09PSAxKSB7XG4gICAgICAgICAgICB0bXBFbC5kb2N1bWVudEVsZW1lbnQuc2V0QXR0cmlidXRlKGF0dHJbMF0sIHRydWUpXG4gICAgICAgICAgfVxuICAgICAgICAgIGVsc2Uge1xuICAgICAgICAgICAgdG1wRWwuZG9jdW1lbnRFbGVtZW50LnNldEF0dHJpYnV0ZShhdHRyWzBdLCBhdHRyWzFdLnNsaWNlKDEsIC0xKSlcbiAgICAgICAgICB9XG4gICAgICAgIH0pXG4gICAgICB9XG4gICAgfVxuXG4gICAgdG1wRWwuZG9jdW1lbnRFbGVtZW50LmlubmVySFRNTCA9IGh0bWxcbiAgICB0aGlzLmxvZyhcImxvYWQgY29udGVudFwiLCB0bXBFbC5kb2N1bWVudEVsZW1lbnQuYXR0cmlidXRlcywgdG1wRWwuZG9jdW1lbnRFbGVtZW50LmlubmVySFRNTC5sZW5ndGgpXG5cbiAgICAvLyBDbGVhciBvdXQgYW55IGZvY3VzZWQgY29udHJvbHMgYmVmb3JlIGluc2VydGluZyBuZXcgcGFnZSBjb250ZW50cy5cbiAgICAvLyB3ZSBjbGVhciBmb2N1cyBvbiBub24gZm9ybSBlbGVtZW50c1xuICAgIGlmIChkb2N1bWVudC5hY3RpdmVFbGVtZW50ICYmICFkb2N1bWVudC5hY3RpdmVFbGVtZW50LnZhbHVlKSB7XG4gICAgICB0cnkge1xuICAgICAgICBkb2N1bWVudC5hY3RpdmVFbGVtZW50LmJsdXIoKVxuICAgICAgfSBjYXRjaCAoZSkgeyB9XG4gICAgfVxuXG4gICAgdGhpcy5zd2l0Y2hTZWxlY3RvcnModGhpcy5vcHRpb25zLnNlbGVjdG9ycywgdG1wRWwsIGRvY3VtZW50LCBvcHRpb25zKVxuXG4gICAgLy9yZXNldCBzdHlsZXNoZWV0cyBpZiBhY3RpdmF0ZWRcbiAgICBpZih0aGlzLm9wdGlvbnMucmVSZW5kZXJDU1MgPT09IHRydWUpe1xuICAgICAgdGhpcy51cGRhdGVTdHlsZXNoZWV0cy5jYWxsKHRoaXMsIHRtcEVsLnF1ZXJ5U2VsZWN0b3JBbGwoJ2xpbmtbcmVsPXN0eWxlc2hlZXRdJyksIGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoJ2xpbmtbcmVsPXN0eWxlc2hlZXRdJykpO1xuICAgIH1cblxuICAgIC8vIEZGIGJ1ZzogV29u4oCZdCBhdXRvZm9jdXMgZmllbGRzIHRoYXQgYXJlIGluc2VydGVkIHZpYSBKUy5cbiAgICAvLyBUaGlzIGJlaGF2aW9yIGlzIGluY29ycmVjdC4gU28gaWYgdGhlcmVzIG5vIGN1cnJlbnQgZm9jdXMsIGF1dG9mb2N1c1xuICAgIC8vIHRoZSBsYXN0IGZpZWxkLlxuICAgIC8vXG4gICAgLy8gaHR0cDovL3d3dy53My5vcmcvaHRtbC93Zy9kcmFmdHMvaHRtbC9tYXN0ZXIvZm9ybXMuaHRtbFxuICAgIHZhciBhdXRvZm9jdXNFbCA9IEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoXCJbYXV0b2ZvY3VzXVwiKSkucG9wKClcbiAgICBpZiAoYXV0b2ZvY3VzRWwgJiYgZG9jdW1lbnQuYWN0aXZlRWxlbWVudCAhPT0gYXV0b2ZvY3VzRWwpIHtcbiAgICAgIGF1dG9mb2N1c0VsLmZvY3VzKCk7XG4gICAgfVxuXG4gICAgLy8gZXhlY3V0ZSBzY3JpcHRzIHdoZW4gRE9NIGhhdmUgYmVlbiBjb21wbGV0ZWx5IHVwZGF0ZWRcbiAgICB0aGlzLm9wdGlvbnMuc2VsZWN0b3JzLmZvckVhY2goIGZ1bmN0aW9uKHNlbGVjdG9yKSB7XG4gICAgICBmb3JFYWNoRWxzKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpLCBmdW5jdGlvbihlbCkge1xuXG4gICAgICAgIGNvbGxlY3RGb3JTY3JpcHRjb21wbGV0ZS5wdXNoLmFwcGx5KGNvbGxlY3RGb3JTY3JpcHRjb21wbGV0ZSwgZXhlY3V0ZVNjcmlwdHMuY2FsbCh0aGlzLCBlbCkpO1xuXG4gICAgICB9LCB0aGlzKTtcblxuICAgIH0sdGhpcyk7XG4gICAgLy8gfVxuICAgIC8vIGNhdGNoKGUpIHtcbiAgICAvLyAgIGlmICh0aGlzLm9wdGlvbnMuZGVidWcpIHtcbiAgICAvLyAgICAgdGhpcy5sb2coXCJQamF4IHN3aXRjaCBmYWlsOiBcIiwgZSlcbiAgICAvLyAgIH1cbiAgICAvLyAgIHRoaXMuc3dpdGNoRmFsbGJhY2sodG1wRWwsIGRvY3VtZW50KVxuICAgIC8vIH1cbiAgICB0aGlzLmxvZyhcIndhaXRpbmcgZm9yIHNjcmlwdGNvbXBsZXRlXCIsY29sbGVjdEZvclNjcmlwdGNvbXBsZXRlKTtcblxuICAgIC8vRmFsbGJhY2shIElmIHNvbWV0aGluZyBjYW4ndCBiZSBsb2FkZWQgb3IgaXMgbm90IGxvYWRlZCBjb3JyZWN0bHkgLT4ganVzdCBmb3JjZSBldmVudGluZyBpbiBlcnJvclxuICAgIHZhciB0aW1lT3V0U2NyaXB0RXZlbnQgPSBudWxsO1xuICAgIHRpbWVPdXRTY3JpcHRFdmVudCA9IHdpbmRvdy5zZXRUaW1lb3V0KCBmdW5jdGlvbigpe1xuICAgICAgdHJpZ2dlcihkb2N1bWVudCxcInBqYXg6c2NyaXB0Y29tcGxldGUgcGpheDpzY3JpcHR0aW1lb3V0XCIsIG9wdGlvbnMpXG4gICAgICB0aW1lT3V0U2NyaXB0RXZlbnQgPSBudWxsO1xuICAgIH0sIHRoaXMub3B0aW9ucy5zY3JpcHRsb2FkdGltZW91dCk7XG5cbiAgICBQcm9taXNlLmFsbChjb2xsZWN0Rm9yU2NyaXB0Y29tcGxldGUpLnRoZW4oXG4gICAgICAvL3Jlc29sdmVkXG4gICAgICBmdW5jdGlvbigpe1xuICAgICAgICBpZih0aW1lT3V0U2NyaXB0RXZlbnQgIT09IG51bGwgKXtcbiAgICAgICAgICB3aW5kb3cuY2xlYXJUaW1lb3V0KHRpbWVPdXRTY3JpcHRFdmVudCk7XG4gICAgICAgICAgdHJpZ2dlcihkb2N1bWVudCxcInBqYXg6c2NyaXB0Y29tcGxldGUgcGpheDpzY3JpcHRzdWNjZXNzXCIsIG9wdGlvbnMpXG4gICAgICAgIH1cbiAgICAgIH0sXG4gICAgICBmdW5jdGlvbigpe1xuICAgICAgICBpZih0aW1lT3V0U2NyaXB0RXZlbnQgIT09IG51bGwgKXtcbiAgICAgICAgICB3aW5kb3cuY2xlYXJUaW1lb3V0KHRpbWVPdXRTY3JpcHRFdmVudCk7XG4gICAgICAgICAgdHJpZ2dlcihkb2N1bWVudCxcInBqYXg6c2NyaXB0Y29tcGxldGUgcGpheDpzY3JpcHRlcnJvclwiLCBvcHRpb25zKVxuICAgICAgICB9XG4gICAgICB9XG4gICAgKTtcblxuXG4gIH0sXG5cbiAgZG9SZXF1ZXN0OiByZXF1aXJlKFwiLi9saWIvcmVxdWVzdC5qc1wiKSxcblxuICBsb2FkVXJsOiBmdW5jdGlvbihocmVmLCBvcHRpb25zKSB7XG4gICAgdGhpcy5sb2coXCJsb2FkIGhyZWZcIiwgaHJlZiwgb3B0aW9ucylcblxuICAgIHRyaWdnZXIoZG9jdW1lbnQsIFwicGpheDpzZW5kXCIsIG9wdGlvbnMpO1xuXG4gICAgLy8gRG8gdGhlIHJlcXVlc3RcbiAgICB0aGlzLmRvUmVxdWVzdChocmVmLCBvcHRpb25zLnJlcXVlc3RPcHRpb25zLCBmdW5jdGlvbihodG1sKSB7XG4gICAgICAvLyBGYWlsIGlmIHVuYWJsZSB0byBsb2FkIEhUTUwgdmlhIEFKQVhcbiAgICAgIGlmIChodG1sID09PSBmYWxzZSkge1xuICAgICAgICB0cmlnZ2VyKGRvY3VtZW50LFwicGpheDpjb21wbGV0ZSBwamF4OmVycm9yXCIsIG9wdGlvbnMpXG5cbiAgICAgICAgcmV0dXJuXG4gICAgICB9XG5cbiAgICAgIC8vIENsZWFyIG91dCBhbnkgZm9jdXNlZCBjb250cm9scyBiZWZvcmUgaW5zZXJ0aW5nIG5ldyBwYWdlIGNvbnRlbnRzLlxuICAgICAgZG9jdW1lbnQuYWN0aXZlRWxlbWVudC5ibHVyKClcblxuICAgICAgdHJ5IHtcbiAgICAgICAgdGhpcy5sb2FkQ29udGVudChodG1sLCBvcHRpb25zKVxuICAgICAgfVxuICAgICAgY2F0Y2ggKGUpIHtcbiAgICAgICAgaWYgKCF0aGlzLm9wdGlvbnMuZGVidWcpIHtcbiAgICAgICAgICBpZiAoY29uc29sZSAmJiB0aGlzLm9wdGlvbnMubG9nT2JqZWN0LmVycm9yKSB7XG4gICAgICAgICAgICB0aGlzLm9wdGlvbnMubG9nT2JqZWN0LmVycm9yKFwiUGpheCBzd2l0Y2ggZmFpbDogXCIsIGUpXG4gICAgICAgICAgfVxuICAgICAgICAgIHRoaXMubGF0ZXN0Q2hhbmNlKGhyZWYpXG4gICAgICAgICAgcmV0dXJuXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgaWYgKHRoaXMub3B0aW9ucy5mb3JjZVJlZGlyZWN0T25GYWlsKSB7XG4gICAgICAgICAgICB0aGlzLmxhdGVzdENoYW5jZShocmVmKTtcbiAgICAgICAgICB9XG4gICAgICAgICAgdGhyb3cgZTtcbiAgICAgICAgfVxuICAgICAgfVxuXG4gICAgICBpZiAob3B0aW9ucy5oaXN0b3J5KSB7XG4gICAgICAgIGlmICh0aGlzLmZpcnN0cnVuKSB7XG4gICAgICAgICAgdGhpcy5sYXN0VWlkID0gdGhpcy5tYXhVaWQgPSBuZXdVaWQoKVxuICAgICAgICAgIHRoaXMuZmlyc3RydW4gPSBmYWxzZVxuICAgICAgICAgIHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSh7XG4gICAgICAgICAgICB1cmw6IHdpbmRvdy5sb2NhdGlvbi5ocmVmLFxuICAgICAgICAgICAgdGl0bGU6IGRvY3VtZW50LnRpdGxlLFxuICAgICAgICAgICAgdWlkOiB0aGlzLm1heFVpZFxuICAgICAgICAgIH0sXG4gICAgICAgICAgZG9jdW1lbnQudGl0bGUpXG4gICAgICAgIH1cblxuICAgICAgICAvLyBVcGRhdGUgYnJvd3NlciBoaXN0b3J5XG4gICAgICAgIHRoaXMubGFzdFVpZCA9IHRoaXMubWF4VWlkID0gbmV3VWlkKClcbiAgICAgICAgd2luZG93Lmhpc3RvcnkucHVzaFN0YXRlKHtcbiAgICAgICAgICB1cmw6IGhyZWYsXG4gICAgICAgICAgdGl0bGU6IG9wdGlvbnMudGl0bGUsXG4gICAgICAgICAgdWlkOiB0aGlzLm1heFVpZFxuICAgICAgICB9LFxuICAgICAgICAgIG9wdGlvbnMudGl0bGUsXG4gICAgICAgICAgaHJlZilcbiAgICAgIH1cblxuICAgICAgdGhpcy5mb3JFYWNoU2VsZWN0b3JzKGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgIHRoaXMucGFyc2VET00oZWwpXG4gICAgICB9LCB0aGlzKVxuXG4gICAgICAvLyBGaXJlIEV2ZW50c1xuICAgICAgdHJpZ2dlcihkb2N1bWVudCxcInBqYXg6Y29tcGxldGUgcGpheDpzdWNjZXNzXCIsIG9wdGlvbnMpXG5cbiAgICAgIG9wdGlvbnMuYW5hbHl0aWNzKClcblxuICAgICAgLy8gU2Nyb2xsIHBhZ2UgdG8gdG9wIG9uIG5ldyBwYWdlIGxvYWRcbiAgICAgIGlmIChvcHRpb25zLnNjcm9sbFRvICE9PSBmYWxzZSkge1xuICAgICAgICBpZiAob3B0aW9ucy5zY3JvbGxUby5sZW5ndGggPiAxKSB7XG4gICAgICAgICAgd2luZG93LnNjcm9sbFRvKG9wdGlvbnMuc2Nyb2xsVG9bMF0sIG9wdGlvbnMuc2Nyb2xsVG9bMV0pXG4gICAgICAgIH1cbiAgICAgICAgZWxzZSB7XG4gICAgICAgICAgd2luZG93LnNjcm9sbFRvKDAsIG9wdGlvbnMuc2Nyb2xsVG8pXG4gICAgICAgIH1cbiAgICAgIH1cbiAgICB9LmJpbmQodGhpcykpXG4gIH1cbn1cblxuUGpheC5pc1N1cHBvcnRlZCA9IHJlcXVpcmUoXCIuL2xpYi9pcy1zdXBwb3J0ZWQuanNcIik7XG5cbi8vYXJndWFibHkgY291bGQgZG8gYGlmKCByZXF1aXJlKFwiLi9saWIvaXMtc3VwcG9ydGVkLmpzXCIpKCkpIHtgIGJ1dCB0aGF0IG1pZ2h0IGJlIGEgbGl0dGxlIHRvIHNpbXBsZVxuaWYgKFBqYXguaXNTdXBwb3J0ZWQoKSkge1xuICBtb2R1bGUuZXhwb3J0cyA9IFBqYXhcbn1cbi8vIGlmIHRoZXJlIGlzbuKAmXQgcmVxdWlyZWQgYnJvd3NlciBmdW5jdGlvbnMsIHJldHVybmluZyBzdHVwaWQgYXBpXG5lbHNlIHtcbiAgdmFyIHN0dXBpZFBqYXggPSBmdW5jdGlvbigpIHt9XG4gIGZvciAodmFyIGtleSBpbiBQamF4LnByb3RvdHlwZSkge1xuICAgIGlmIChQamF4LnByb3RvdHlwZS5oYXNPd25Qcm9wZXJ0eShrZXkpICYmIHR5cGVvZiBQamF4LnByb3RvdHlwZVtrZXldID09PSBcImZ1bmN0aW9uXCIpIHtcbiAgICAgIHN0dXBpZFBqYXhba2V5XSA9IHN0dXBpZFBqYXhcbiAgICB9XG4gIH1cblxuICBtb2R1bGUuZXhwb3J0cyA9IHN0dXBpZFBqYXhcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24ob2JqKSB7XG4gIGlmIChudWxsID09PSBvYmogfHwgXCJvYmplY3RcIiAhPSB0eXBlb2Ygb2JqKSB7XG4gICAgcmV0dXJuIG9ialxuICB9XG4gIHZhciBjb3B5ID0gb2JqLmNvbnN0cnVjdG9yKClcbiAgZm9yICh2YXIgYXR0ciBpbiBvYmopIHtcbiAgICBpZiAob2JqLmhhc093blByb3BlcnR5KGF0dHIpKSB7XG4gICAgICBjb3B5W2F0dHJdID0gb2JqW2F0dHJdXG4gICAgfVxuICB9XG4gIHJldHVybiBjb3B5XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciBxdWVyeVNlbGVjdG9yID0gdGhpcy5vcHRpb25zLm1haW5TY3JpcHRFbGVtZW50O1xuICB2YXIgY29kZSA9IChlbC50ZXh0IHx8IGVsLnRleHRDb250ZW50IHx8IGVsLmlubmVySFRNTCB8fCBcIlwiKVxuICB2YXIgc3JjID0gKGVsLnNyYyB8fCBcIlwiKTtcbiAgdmFyIHBhcmVudCA9IGVsLnBhcmVudE5vZGUgfHwgZG9jdW1lbnQucXVlcnlTZWxlY3RvcihxdWVyeVNlbGVjdG9yKSB8fCBkb2N1bWVudC5kb2N1bWVudEVsZW1lbnRcbiAgdmFyIHNjcmlwdCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoXCJzY3JpcHRcIilcbiAgdmFyIHByb21pc2UgPSBudWxsO1xuXG4gIHRoaXMubG9nKFwiRXZhbHVhdGluZyBTY3JpcHQ6IFwiLCBlbCk7XG5cbiAgaWYgKGNvZGUubWF0Y2goXCJkb2N1bWVudC53cml0ZVwiKSkge1xuICAgIGlmIChjb25zb2xlICYmIHRoaXMub3B0aW9ucy5sb2dPYmplY3QubG9nKSB7XG4gICAgICB0aGlzLm9wdGlvbnMubG9nT2JqZWN0LmxvZyhcIlNjcmlwdCBjb250YWlucyBkb2N1bWVudC53cml0ZS4gQ2Fu4oCZdCBiZSBleGVjdXRlZCBjb3JyZWN0bHkuIENvZGUgc2tpcHBlZCBcIiwgZWwpXG4gICAgfVxuICAgIHJldHVybiBmYWxzZVxuICB9XG5cbiAgcHJvbWlzZSA9IG5ldyBQcm9taXNlKCBmdW5jdGlvbihyZXNvbHZlLCByZWplY3Qpe1xuXG4gICAgc2NyaXB0LnR5cGUgPSBcInRleHQvamF2YXNjcmlwdFwiXG4gICAgaWYgKHNyYyAhPSBcIlwiKSB7XG4gICAgICBzY3JpcHQuc3JjID0gc3JjO1xuICAgICAgc2NyaXB0LmFkZEV2ZW50TGlzdGVuZXIoJ2xvYWQnLCBmdW5jdGlvbigpe3Jlc29sdmUoc3JjKTt9ICk7XG4gICAgICBzY3JpcHQuYXN5bmMgPSB0cnVlOyAvLyBmb3JjZSBhc3luY2hyb25vdXMgbG9hZGluZyBvZiBwZXJpcGhlcmFsIGpzXG4gICAgfVxuXG4gICAgaWYgKGNvZGUgIT0gXCJcIikge1xuICAgICAgdHJ5IHtcbiAgICAgICAgc2NyaXB0LmFwcGVuZENoaWxkKGRvY3VtZW50LmNyZWF0ZVRleHROb2RlKGNvZGUpKVxuICAgICAgfVxuICAgICAgY2F0Y2ggKGUpIHtcbiAgICAgICAgLy8gb2xkIElFcyBoYXZlIGZ1bmt5IHNjcmlwdCBub2Rlc1xuICAgICAgICBzY3JpcHQudGV4dCA9IGNvZGVcbiAgICAgIH1cbiAgICAgIHJlc29sdmUoJ3RleHQtbm9kZScpO1xuICAgIH1cbiAgfSk7XG5cbiAgdGhpcy5sb2coJ1BhcmVudEVsZW1lbnQgPT4gJywgcGFyZW50ICk7XG5cbiAgLy8gZXhlY3V0ZVxuICBwYXJlbnQuYXBwZW5kQ2hpbGQoc2NyaXB0KTtcbiAgcGFyZW50LnJlbW92ZUNoaWxkKHNjcmlwdClcbiAgLy8gYXZvaWQgcG9sbHV0aW9uIG9ubHkgaW4gaGVhZCBvciBib2R5IHRhZ3NcbiAgLy8gb2YgaWYgdGhlIHNldHRpbmcgcmVtb3ZlU2NyaXB0c0FmdGVyUGFyc2luZyBpcyBhY3RpdmVcbiAgaWYoIChbXCJoZWFkXCIsXCJib2R5XCJdLmluZGV4T2YoIHBhcmVudC50YWdOYW1lLnRvTG93ZXJDYXNlKCkpID4gMCkgfHwgKHRoaXMub3B0aW9ucy5yZW1vdmVTY3JpcHRzQWZ0ZXJQYXJzaW5nID09PSB0cnVlKSApIHtcbiAgfVxuXG4gIHJldHVybiBwcm9taXNlO1xufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbHMsIGV2ZW50cywgbGlzdGVuZXIsIHVzZUNhcHR1cmUpIHtcbiAgZXZlbnRzID0gKHR5cGVvZiBldmVudHMgPT09IFwic3RyaW5nXCIgPyBldmVudHMuc3BsaXQoXCIgXCIpIDogZXZlbnRzKVxuXG4gIGV2ZW50cy5mb3JFYWNoKGZ1bmN0aW9uKGUpIHtcbiAgICBmb3JFYWNoRWxzKGVscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIGVsLnJlbW92ZUV2ZW50TGlzdGVuZXIoZSwgbGlzdGVuZXIsIHVzZUNhcHR1cmUpXG4gICAgfSlcbiAgfSlcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4uL2ZvcmVhY2gtZWxzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxzLCBldmVudHMsIGxpc3RlbmVyLCB1c2VDYXB0dXJlKSB7XG4gIGV2ZW50cyA9ICh0eXBlb2YgZXZlbnRzID09PSBcInN0cmluZ1wiID8gZXZlbnRzLnNwbGl0KFwiIFwiKSA6IGV2ZW50cylcblxuICBldmVudHMuZm9yRWFjaChmdW5jdGlvbihlKSB7XG4gICAgZm9yRWFjaEVscyhlbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICBlbC5hZGRFdmVudExpc3RlbmVyKGUsIGxpc3RlbmVyLCB1c2VDYXB0dXJlKVxuICAgIH0pXG4gIH0pXG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVscywgZXZlbnRzLCBvcHRzKSB7XG4gIGV2ZW50cyA9ICh0eXBlb2YgZXZlbnRzID09PSBcInN0cmluZ1wiID8gZXZlbnRzLnNwbGl0KFwiIFwiKSA6IGV2ZW50cylcblxuICBldmVudHMuZm9yRWFjaChmdW5jdGlvbihlKSB7XG4gICAgdmFyIGV2ZW50IC8vID0gbmV3IEN1c3RvbUV2ZW50KGUpIC8vIGRvZXNuJ3QgZXZlcnl3aGVyZSB5ZXRcbiAgICBldmVudCA9IGRvY3VtZW50LmNyZWF0ZUV2ZW50KFwiSFRNTEV2ZW50c1wiKVxuICAgIGV2ZW50LmluaXRFdmVudChlLCB0cnVlLCB0cnVlKVxuICAgIGV2ZW50LmV2ZW50TmFtZSA9IGVcbiAgICBpZiAob3B0cykge1xuICAgICAgT2JqZWN0LmtleXMob3B0cykuZm9yRWFjaChmdW5jdGlvbihrZXkpIHtcbiAgICAgICAgZXZlbnRba2V5XSA9IG9wdHNba2V5XVxuICAgICAgfSlcbiAgICB9XG5cbiAgICBmb3JFYWNoRWxzKGVscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIHZhciBkb21GaXggPSBmYWxzZVxuICAgICAgaWYgKCFlbC5wYXJlbnROb2RlICYmIGVsICE9PSBkb2N1bWVudCAmJiBlbCAhPT0gd2luZG93KSB7XG4gICAgICAgIC8vIFRIQU5LUyBZT1UgSUUgKDkvMTAvLzExIGNvbmNlcm5lZClcbiAgICAgICAgLy8gZGlzcGF0Y2hFdmVudCBkb2Vzbid0IHdvcmsgaWYgZWxlbWVudCBpcyBub3QgaW4gdGhlIGRvbVxuICAgICAgICBkb21GaXggPSB0cnVlXG4gICAgICAgIGRvY3VtZW50LmJvZHkuYXBwZW5kQ2hpbGQoZWwpXG4gICAgICB9XG4gICAgICBlbC5kaXNwYXRjaEV2ZW50KGV2ZW50KVxuICAgICAgaWYgKGRvbUZpeCkge1xuICAgICAgICBlbC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKGVsKVxuICAgICAgfVxuICAgIH0pXG4gIH0pXG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2ZvcmVhY2gtZWxzXCIpXG52YXIgZXZhbFNjcmlwdCA9IHJlcXVpcmUoXCIuL2V2YWwtc2NyaXB0XCIpXG4vLyBGaW5kcyBhbmQgZXhlY3V0ZXMgc2NyaXB0cyAodXNlZCBmb3IgbmV3bHkgYWRkZWQgZWxlbWVudHMpXG4vLyBOZWVkZWQgc2luY2UgaW5uZXJIVE1MIGRvZXMgbm90IHJ1biBzY3JpcHRzXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG5cbiAgdGhpcy5sb2coXCJFeGVjdXRpbmcgc2NyaXB0cyBmb3IgXCIsIGVsKTtcblxuICB2YXIgbG9hZGluZ1NjcmlwdHMgPSBbXTtcblxuICBpZihlbCA9PT0gdW5kZWZpbmVkKSByZXR1cm4gUHJvbWlzZS5yZXNvbHZlKCk7XG5cbiAgaWYgKGVsLnRhZ05hbWUudG9Mb3dlckNhc2UoKSA9PT0gXCJzY3JpcHRcIikge1xuICAgIGV2YWxTY3JpcHQuY2FsbCh0aGlzLCBlbCk7XG4gIH1cblxuICBmb3JFYWNoRWxzKGVsLnF1ZXJ5U2VsZWN0b3JBbGwoXCJzY3JpcHRcIiksIGZ1bmN0aW9uKHNjcmlwdCkge1xuICAgIGlmICghc2NyaXB0LnR5cGUgfHwgc2NyaXB0LnR5cGUudG9Mb3dlckNhc2UoKSA9PT0gXCJ0ZXh0L2phdmFzY3JpcHRcIikge1xuICAgICAgLy8gaWYgKHNjcmlwdC5wYXJlbnROb2RlKSB7XG4gICAgICAvLyAgIHNjcmlwdC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKHNjcmlwdClcbiAgICAgIC8vIH1cbiAgICAgIGxvYWRpbmdTY3JpcHRzLnB1c2goZXZhbFNjcmlwdC5jYWxsKHRoaXMsIHNjcmlwdCkpO1xuICAgIH1cbiAgfSwgdGhpcyk7XG5cbiAgcmV0dXJuIGxvYWRpbmdTY3JpcHRzO1xufVxuIiwiLyogZ2xvYmFsIEhUTUxDb2xsZWN0aW9uOiB0cnVlICovXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxzLCBmbiwgY29udGV4dCkge1xuICBpZiAoZWxzIGluc3RhbmNlb2YgSFRNTENvbGxlY3Rpb24gfHwgZWxzIGluc3RhbmNlb2YgTm9kZUxpc3QgfHwgZWxzIGluc3RhbmNlb2YgQXJyYXkpIHtcbiAgICByZXR1cm4gQXJyYXkucHJvdG90eXBlLmZvckVhY2guY2FsbChlbHMsIGZuLCBjb250ZXh0KVxuICB9XG4gIC8vIGFzc3VtZSBzaW1wbGUgZG9tIGVsZW1lbnRcbiAgcmV0dXJuIGZuLmNhbGwoY29udGV4dCwgZWxzKVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9mb3JlYWNoLWVsc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKHNlbGVjdG9ycywgY2IsIGNvbnRleHQsIERPTWNvbnRleHQpIHtcbiAgRE9NY29udGV4dCA9IERPTWNvbnRleHQgfHwgZG9jdW1lbnRcbiAgc2VsZWN0b3JzLmZvckVhY2goZnVuY3Rpb24oc2VsZWN0b3IpIHtcbiAgICBmb3JFYWNoRWxzKERPTWNvbnRleHQucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvciksIGNiLCBjb250ZXh0KVxuICB9KVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbigpIHtcbiAgLy8gQm9ycm93ZWQgd2hvbGVzYWxlIGZyb20gaHR0cHM6Ly9naXRodWIuY29tL2RlZnVua3QvanF1ZXJ5LXBqYXhcbiAgcmV0dXJuIHdpbmRvdy5oaXN0b3J5ICYmXG4gICAgd2luZG93Lmhpc3RvcnkucHVzaFN0YXRlICYmXG4gICAgd2luZG93Lmhpc3RvcnkucmVwbGFjZVN0YXRlICYmXG4gICAgLy8gcHVzaFN0YXRlIGlzbuKAmXQgcmVsaWFibGUgb24gaU9TIHVudGlsIDUuXG4gICAgIW5hdmlnYXRvci51c2VyQWdlbnQubWF0Y2goLygoaVBvZHxpUGhvbmV8aVBhZCkuK1xcYk9TXFxzK1sxLTRdXFxEfFdlYkFwcHNcXC8uK0NGTmV0d29yaykvKVxufVxuIiwiaWYgKCFGdW5jdGlvbi5wcm90b3R5cGUuYmluZCkge1xuICBGdW5jdGlvbi5wcm90b3R5cGUuYmluZCA9IGZ1bmN0aW9uKG9UaGlzKSB7XG4gICAgaWYgKHR5cGVvZiB0aGlzICE9PSBcImZ1bmN0aW9uXCIpIHtcbiAgICAgIC8vIGNsb3Nlc3QgdGhpbmcgcG9zc2libGUgdG8gdGhlIEVDTUFTY3JpcHQgNSBpbnRlcm5hbCBJc0NhbGxhYmxlIGZ1bmN0aW9uXG4gICAgICB0aHJvdyBuZXcgVHlwZUVycm9yKFwiRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQgLSB3aGF0IGlzIHRyeWluZyB0byBiZSBib3VuZCBpcyBub3QgY2FsbGFibGVcIilcbiAgICB9XG5cbiAgICB2YXIgYUFyZ3MgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMsIDEpXG4gICAgdmFyIHRoYXQgPSB0aGlzXG4gICAgdmFyIEZub29wID0gZnVuY3Rpb24oKSB7fVxuICAgIHZhciBmQm91bmQgPSBmdW5jdGlvbigpIHtcbiAgICAgIHJldHVybiB0aGF0LmFwcGx5KHRoaXMgaW5zdGFuY2VvZiBGbm9vcCAmJiBvVGhpcyA/IHRoaXMgOiBvVGhpcywgYUFyZ3MuY29uY2F0KEFycmF5LnByb3RvdHlwZS5zbGljZS5jYWxsKGFyZ3VtZW50cykpKVxuICAgIH1cblxuICAgIEZub29wLnByb3RvdHlwZSA9IHRoaXMucHJvdG90eXBlXG4gICAgZkJvdW5kLnByb3RvdHlwZSA9IG5ldyBGbm9vcCgpXG5cbiAgICByZXR1cm4gZkJvdW5kXG4gIH1cbn1cbiIsInJlcXVpcmUoXCIuLi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmRcIilcblxudmFyIG9uID0gcmVxdWlyZShcIi4uL2V2ZW50cy9vblwiKVxudmFyIGNsb25lID0gcmVxdWlyZShcIi4uL2Nsb25lXCIpXG5cbnZhciBhdHRyQ2xpY2sgPSBcImRhdGEtcGpheC1zdWJtaXQtc3RhdGVcIlxuXG52YXIgZm9ybUFjdGlvbiA9IGZ1bmN0aW9uKGVsLCBldmVudCl7XG5cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zID0ge1xuICAgIHJlcXVlc3RVcmwgOiBlbC5nZXRBdHRyaWJ1dGUoJ2FjdGlvbicpIHx8IHdpbmRvdy5sb2NhdGlvbi5ocmVmLFxuICAgIHJlcXVlc3RNZXRob2QgOiBlbC5nZXRBdHRyaWJ1dGUoJ21ldGhvZCcpIHx8ICdHRVQnLFxuICB9XG5cbiAgLy9jcmVhdGUgYSB0ZXN0YWJsZSB2aXJ0dWFsIGxpbmsgb2YgdGhlIGZvcm0gYWN0aW9uXG4gIHZhciB2aXJ0TGlua0VsZW1lbnQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdhJyk7XG4gIHZpcnRMaW5rRWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2hyZWYnLCB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFVybCk7XG5cbiAgLy8gSWdub3JlIGV4dGVybmFsIGxpbmtzLlxuICBpZiAodmlydExpbmtFbGVtZW50LnByb3RvY29sICE9PSB3aW5kb3cubG9jYXRpb24ucHJvdG9jb2wgfHwgdmlydExpbmtFbGVtZW50Lmhvc3QgIT09IHdpbmRvdy5sb2NhdGlvbi5ob3N0KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJleHRlcm5hbFwiKTtcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBjbGljayBpZiB3ZSBhcmUgb24gYW4gYW5jaG9yIG9uIHRoZSBzYW1lIHBhZ2VcbiAgaWYgKHZpcnRMaW5rRWxlbWVudC5wYXRobmFtZSA9PT0gd2luZG93LmxvY2F0aW9uLnBhdGhuYW1lICYmIHZpcnRMaW5rRWxlbWVudC5oYXNoLmxlbmd0aCA+IDApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1wcmVzZW50XCIpO1xuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGVtcHR5IGFuY2hvciBcImZvby5odG1sI1wiXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdICsgXCIjXCIpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1lbXB0eVwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gaWYgZGVjbGFyZWQgYXMgYSBmdWxsIHJlbG9hZCwganVzdCBub3JtYWxseSBzdWJtaXQgdGhlIGZvcm1cbiAgaWYgKCB0aGlzLm9wdGlvbnMuY3VycmVudFVybEZ1bGxSZWxvYWQpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInJlbG9hZFwiKTtcbiAgICByZXR1cm47XG4gIH1cblxuICBldmVudC5wcmV2ZW50RGVmYXVsdCgpXG4gIHZhciBuYW1lTGlzdCA9IFtdO1xuICB2YXIgcGFyYW1PYmplY3QgPSBbXTtcbiAgZm9yKHZhciBlbGVtZW50S2V5IGluIGVsLmVsZW1lbnRzKSB7XG4gICAgdmFyIGVsZW1lbnQgPSBlbC5lbGVtZW50c1tlbGVtZW50S2V5XTtcbiAgICBpZiAoISFlbGVtZW50Lm5hbWUgJiYgZWxlbWVudC5hdHRyaWJ1dGVzICE9PSB1bmRlZmluZWQgJiYgZWxlbWVudC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgIT09ICdidXR0b24nKXtcbiAgICAgIGlmIChcbiAgICAgICAgKGVsZW1lbnQudHlwZSAhPT0gJ2NoZWNrYm94JyAmJiBlbGVtZW50LnR5cGUgIT09ICdyYWRpbycpIHx8IGVsZW1lbnQuY2hlY2tlZFxuICAgICAgKSB7XG4gICAgICAgIGlmKG5hbWVMaXN0LmluZGV4T2YoZWxlbWVudC5uYW1lKSA9PT0gLTEpe1xuICAgICAgICAgIG5hbWVMaXN0LnB1c2goZWxlbWVudC5uYW1lKTtcbiAgICAgICAgICBwYXJhbU9iamVjdC5wdXNoKHsgbmFtZTogZW5jb2RlVVJJQ29tcG9uZW50KGVsZW1lbnQubmFtZSksIHZhbHVlOiBlbmNvZGVVUklDb21wb25lbnQoZWxlbWVudC52YWx1ZSl9KTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfVxuXG5cblxuICAvL0NyZWF0aW5nIGEgZ2V0U3RyaW5nXG4gIHZhciBwYXJhbXNTdHJpbmcgPSAocGFyYW1PYmplY3QubWFwKGZ1bmN0aW9uKHZhbHVlKXtyZXR1cm4gdmFsdWUubmFtZStcIj1cIit2YWx1ZS52YWx1ZTt9KSkuam9pbignJicpO1xuXG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0UGF5bG9hZCA9IHBhcmFtT2JqZWN0O1xuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgPSBwYXJhbXNTdHJpbmc7XG5cbiAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJzdWJtaXRcIik7XG5cbiAgdGhpcy5sb2FkVXJsKHZpcnRMaW5rRWxlbWVudC5ocmVmLCBjbG9uZSh0aGlzLm9wdGlvbnMpKVxuXG59O1xuXG52YXIgaXNEZWZhdWx0UHJldmVudGVkID0gZnVuY3Rpb24oZXZlbnQpIHtcbiAgcmV0dXJuIGV2ZW50LmRlZmF1bHRQcmV2ZW50ZWQgfHwgZXZlbnQucmV0dXJuVmFsdWUgPT09IGZhbHNlO1xufTtcblxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciB0aGF0ID0gdGhpc1xuXG4gIG9uKGVsLCBcInN1Ym1pdFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBmb3JtQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICB9KVxufVxuIiwicmVxdWlyZShcIi4uL3BvbHlmaWxscy9GdW5jdGlvbi5wcm90b3R5cGUuYmluZFwiKVxuXG52YXIgb24gPSByZXF1aXJlKFwiLi4vZXZlbnRzL29uXCIpXG52YXIgY2xvbmUgPSByZXF1aXJlKFwiLi4vY2xvbmVcIilcblxudmFyIGF0dHJDbGljayA9IFwiZGF0YS1wamF4LWNsaWNrLXN0YXRlXCJcbnZhciBhdHRyS2V5ID0gXCJkYXRhLXBqYXgta2V5dXAtc3RhdGVcIlxuXG52YXIgbGlua0FjdGlvbiA9IGZ1bmN0aW9uKGVsLCBldmVudCkge1xuICAvLyBEb27igJl0IGJyZWFrIGJyb3dzZXIgc3BlY2lhbCBiZWhhdmlvciBvbiBsaW5rcyAobGlrZSBwYWdlIGluIG5ldyB3aW5kb3cpXG4gIGlmIChldmVudC53aGljaCA+IDEgfHwgZXZlbnQubWV0YUtleSB8fCBldmVudC5jdHJsS2V5IHx8IGV2ZW50LnNoaWZ0S2V5IHx8IGV2ZW50LmFsdEtleSkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwibW9kaWZpZXJcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIHdlIGRvIHRlc3Qgb24gaHJlZiBub3cgdG8gcHJldmVudCB1bmV4cGVjdGVkIGJlaGF2aW9yIGlmIGZvciBzb21lIHJlYXNvblxuICAvLyB1c2VyIGhhdmUgaHJlZiB0aGF0IGNhbiBiZSBkeW5hbWljYWxseSB1cGRhdGVkXG5cbiAgLy8gSWdub3JlIGV4dGVybmFsIGxpbmtzLlxuICBpZiAoZWwucHJvdG9jb2wgIT09IHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCB8fCBlbC5ob3N0ICE9PSB3aW5kb3cubG9jYXRpb24uaG9zdCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiZXh0ZXJuYWxcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBjbGljayBpZiB3ZSBhcmUgb24gYW4gYW5jaG9yIG9uIHRoZSBzYW1lIHBhZ2VcbiAgaWYgKGVsLnBhdGhuYW1lID09PSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgJiYgZWwuaGFzaC5sZW5ndGggPiAwKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItcHJlc2VudFwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGFuY2hvcnMgb24gdGhlIHNhbWUgcGFnZSAoa2VlcCBuYXRpdmUgYmVoYXZpb3IpXG4gIGlmIChlbC5oYXNoICYmIGVsLmhyZWYucmVwbGFjZShlbC5oYXNoLCBcIlwiKSA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYucmVwbGFjZShsb2NhdGlvbi5oYXNoLCBcIlwiKSkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yXCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgZW1wdHkgYW5jaG9yIFwiZm9vLmh0bWwjXCJcbiAgaWYgKGVsLmhyZWYgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnNwbGl0KFwiI1wiKVswXSArIFwiI1wiKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItZW1wdHlcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIGV2ZW50LnByZXZlbnREZWZhdWx0KClcblxuICAvLyBkb27igJl0IGRvIFwibm90aGluZ1wiIGlmIHVzZXIgdHJ5IHRvIHJlbG9hZCB0aGUgcGFnZSBieSBjbGlja2luZyB0aGUgc2FtZSBsaW5rIHR3aWNlXG4gIGlmIChcbiAgICB0aGlzLm9wdGlvbnMuY3VycmVudFVybEZ1bGxSZWxvYWQgJiZcbiAgICBlbC5ocmVmID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5zcGxpdChcIiNcIilbMF1cbiAgKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJyZWxvYWRcIilcbiAgICB0aGlzLnJlbG9hZCgpXG4gICAgcmV0dXJuXG4gIH1cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zID0gdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zIHx8IHt9O1xuICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImxvYWRcIilcbiAgdGhpcy5sb2FkVXJsKGVsLmhyZWYsIGNsb25lKHRoaXMub3B0aW9ucykpXG59XG5cbnZhciBpc0RlZmF1bHRQcmV2ZW50ZWQgPSBmdW5jdGlvbihldmVudCkge1xuICByZXR1cm4gZXZlbnQuZGVmYXVsdFByZXZlbnRlZCB8fCBldmVudC5yZXR1cm5WYWx1ZSA9PT0gZmFsc2U7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgdmFyIHRoYXQgPSB0aGlzXG5cbiAgb24oZWwsIFwiY2xpY2tcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgbGlua0FjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgfSlcblxuICBvbihlbCwgXCJrZXl1cFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICAvLyBEb27igJl0IGJyZWFrIGJyb3dzZXIgc3BlY2lhbCBiZWhhdmlvciBvbiBsaW5rcyAobGlrZSBwYWdlIGluIG5ldyB3aW5kb3cpXG4gICAgaWYgKGV2ZW50LndoaWNoID4gMSB8fCBldmVudC5tZXRhS2V5IHx8IGV2ZW50LmN0cmxLZXkgfHwgZXZlbnQuc2hpZnRLZXkgfHwgZXZlbnQuYWx0S2V5KSB7XG4gICAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0cktleSwgXCJtb2RpZmllclwiKVxuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgaWYgKGV2ZW50LmtleUNvZGUgPT0gMTMpIHtcbiAgICAgIGxpbmtBY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gICAgfVxuICB9LmJpbmQodGhpcykpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHJldHVybiBlbC5xdWVyeVNlbGVjdG9yQWxsKHRoaXMub3B0aW9ucy5lbGVtZW50cylcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oKSB7XG4gIGlmICgodGhpcy5vcHRpb25zLmRlYnVnICYmIHRoaXMub3B0aW9ucy5sb2dPYmplY3QpKSB7XG4gICAgaWYgKHR5cGVvZiB0aGlzLm9wdGlvbnMubG9nT2JqZWN0LmxvZyA9PT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICB0aGlzLm9wdGlvbnMubG9nT2JqZWN0LmxvZy5hcHBseSh0aGlzLm9wdGlvbnMubG9nT2JqZWN0LCBbJ1BKQVggLT4nLGFyZ3VtZW50c10pO1xuICAgIH1cbiAgICAvLyBpZSBpcyB3ZWlyZFxuICAgIGVsc2UgaWYgKHRoaXMub3B0aW9ucy5sb2dPYmplY3QubG9nKSB7XG4gICAgICB0aGlzLm9wdGlvbnMubG9nT2JqZWN0LmxvZyhbJ1BKQVggLT4nLGFyZ3VtZW50c10pO1xuICAgIH1cbiAgfVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxudmFyIHBhcnNlRWxlbWVudFVubG9hZCA9IHJlcXVpcmUoXCIuL3BhcnNlLWVsZW1lbnQtdW5sb2FkXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgZm9yRWFjaEVscyh0aGlzLmdldEVsZW1lbnRzKGVsKSwgcGFyc2VFbGVtZW50VW5sb2FkLCB0aGlzKVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxudmFyIHBhcnNlRWxlbWVudCA9IHJlcXVpcmUoXCIuL3BhcnNlLWVsZW1lbnRcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICBmb3JFYWNoRWxzKHRoaXMuZ2V0RWxlbWVudHMoZWwpLCBwYXJzZUVsZW1lbnQsIHRoaXMpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHN3aXRjaCAoZWwudGFnTmFtZS50b0xvd2VyQ2FzZSgpKSB7XG4gIGNhc2UgXCJhXCI6XG4gICAgLy8gb25seSBhdHRhY2ggbGluayBpZiBlbCBkb2VzIG5vdCBhbHJlYWR5IGhhdmUgbGluayBhdHRhY2hlZFxuICAgIGlmICghZWwuaGFzQXR0cmlidXRlKCdkYXRhLXBqYXgtY2xpY2stc3RhdGUnKSkge1xuICAgICAgdGhpcy51bmF0dGFjaExpbmsoZWwpXG4gICAgfVxuICAgIGJyZWFrXG5cbiAgICBjYXNlIFwiZm9ybVwiOlxuICAgICAgLy8gb25seSBhdHRhY2ggbGluayBpZiBlbCBkb2VzIG5vdCBhbHJlYWR5IGhhdmUgbGluayBhdHRhY2hlZFxuICAgICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICAgIHRoaXMudW5hdHRhY2hGb3JtKGVsKVxuICAgICAgfVxuICAgIGJyZWFrXG5cbiAgZGVmYXVsdDpcbiAgICB0aHJvdyBcIlBqYXggY2FuIG9ubHkgYmUgYXBwbGllZCBvbiA8YT4gb3IgPGZvcm0+IHN1Ym1pdFwiXG4gIH1cbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgc3dpdGNoIChlbC50YWdOYW1lLnRvTG93ZXJDYXNlKCkpIHtcbiAgY2FzZSBcImFcIjpcbiAgICAvLyBvbmx5IGF0dGFjaCBsaW5rIGlmIGVsIGRvZXMgbm90IGFscmVhZHkgaGF2ZSBsaW5rIGF0dGFjaGVkXG4gICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICB0aGlzLmF0dGFjaExpbmsoZWwpXG4gICAgfVxuICAgIGJyZWFrXG5cbiAgICBjYXNlIFwiZm9ybVwiOlxuICAgICAgLy8gb25seSBhdHRhY2ggbGluayBpZiBlbCBkb2VzIG5vdCBhbHJlYWR5IGhhdmUgbGluayBhdHRhY2hlZFxuICAgICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICAgIHRoaXMuYXR0YWNoRm9ybShlbClcbiAgICAgIH1cbiAgICBicmVha1xuXG4gIGRlZmF1bHQ6XG4gICAgdGhyb3cgXCJQamF4IGNhbiBvbmx5IGJlIGFwcGxpZWQgb24gPGE+IG9yIDxmb3JtPiBzdWJtaXRcIlxuICB9XG59XG4iLCIvKiBnbG9iYWwgX2dhcTogdHJ1ZSwgZ2E6IHRydWUgKi9cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihvcHRpb25zKXtcbiAgdGhpcy5vcHRpb25zID0gb3B0aW9uc1xuICB0aGlzLm9wdGlvbnMuZWxlbWVudHMgPSB0aGlzLm9wdGlvbnMuZWxlbWVudHMgfHwgXCJhW2hyZWZdLCBmb3JtW2FjdGlvbl1cIixcbiAgdGhpcy5vcHRpb25zLnJlUmVuZGVyQ1NTID0gdGhpcy5vcHRpb25zLnJlUmVuZGVyQ1NTIHx8IGZhbHNlLFxuICB0aGlzLm9wdGlvbnMuZm9yY2VSZWRpcmVjdE9uRmFpbCA9IHRoaXMub3B0aW9ucy5mb3JjZVJlZGlyZWN0T25GYWlsIHx8IGZhbHNlLFxuICB0aGlzLm9wdGlvbnMuc2NyaXB0bG9hZHRpbWVvdXQgPSB0aGlzLm9wdGlvbnMuc2NyaXB0bG9hZHRpbWVvdXQgfHwgMTAwMCxcbiAgdGhpcy5vcHRpb25zLm1haW5TY3JpcHRFbGVtZW50ID0gdGhpcy5vcHRpb25zLm1haW5TY3JpcHRFbGVtZW50IHx8IFwiaGVhZFwiXG4gIHRoaXMub3B0aW9ucy5yZW1vdmVTY3JpcHRzQWZ0ZXJQYXJzaW5nID0gdGhpcy5vcHRpb25zLnJlbW92ZVNjcmlwdHNBZnRlclBhcnNpbmcgfHwgdHJ1ZVxuICB0aGlzLm9wdGlvbnMubG9nT2JqZWN0ID0gdGhpcy5vcHRpb25zLmxvZ09iamVjdCB8fCBjb25zb2xlXG4gIHRoaXMub3B0aW9ucy5zZWxlY3RvcnMgPSB0aGlzLm9wdGlvbnMuc2VsZWN0b3JzIHx8IFtcInRpdGxlXCIsIFwiLmpzLVBqYXhcIl1cbiAgdGhpcy5vcHRpb25zLnN3aXRjaGVzID0gdGhpcy5vcHRpb25zLnN3aXRjaGVzIHx8IHt9XG4gIHRoaXMub3B0aW9ucy5zd2l0Y2hlc09wdGlvbnMgPSB0aGlzLm9wdGlvbnMuc3dpdGNoZXNPcHRpb25zIHx8IHt9XG4gIHRoaXMub3B0aW9ucy5oaXN0b3J5ID0gdGhpcy5vcHRpb25zLmhpc3RvcnkgfHwgdHJ1ZVxuICB0aGlzLm9wdGlvbnMuYW5hbHl0aWNzID0gdGhpcy5vcHRpb25zLmFuYWx5dGljcyB8fCBmdW5jdGlvbigpIHtcbiAgICAvLyBvcHRpb25zLmJhY2t3YXJkIG9yIG9wdGlvbnMuZm93YXJkIGNhbiBiZSB0cnVlIG9yIHVuZGVmaW5lZFxuICAgIC8vIGJ5IGRlZmF1bHQsIHdlIGRvIHRyYWNrIGJhY2svZm93YXJkIGhpdFxuICAgIC8vIGh0dHBzOi8vcHJvZHVjdGZvcnVtcy5nb29nbGUuY29tL2ZvcnVtLyMhdG9waWMvYW5hbHl0aWNzL1dWd01EakxoWFlrXG4gICAgaWYgKHdpbmRvdy5fZ2FxKSB7XG4gICAgICBfZ2FxLnB1c2goW1wiX3RyYWNrUGFnZXZpZXdcIl0pXG4gICAgfVxuICAgIGlmICh3aW5kb3cuZ2EpIHtcbiAgICAgIGdhKFwic2VuZFwiLCBcInBhZ2V2aWV3XCIsIHtwYWdlOiBsb2NhdGlvbi5wYXRobmFtZSwgdGl0bGU6IGRvY3VtZW50LnRpdGxlfSlcbiAgICB9XG4gIH1cbiAgdGhpcy5vcHRpb25zLnNjcm9sbFRvID0gKHR5cGVvZiB0aGlzLm9wdGlvbnMuc2Nyb2xsVG8gPT09ICd1bmRlZmluZWQnKSA/IDAgOiB0aGlzLm9wdGlvbnMuc2Nyb2xsVG87XG4gIHRoaXMub3B0aW9ucy5jYWNoZUJ1c3QgPSAodHlwZW9mIHRoaXMub3B0aW9ucy5jYWNoZUJ1c3QgPT09ICd1bmRlZmluZWQnKSA/IHRydWUgOiB0aGlzLm9wdGlvbnMuY2FjaGVCdXN0XG4gIHRoaXMub3B0aW9ucy5kZWJ1ZyA9IHRoaXMub3B0aW9ucy5kZWJ1ZyB8fCBmYWxzZVxuXG4gIC8vIHdlIGNhbuKAmXQgcmVwbGFjZSBib2R5Lm91dGVySFRNTCBvciBoZWFkLm91dGVySFRNTFxuICAvLyBpdCBjcmVhdGUgYSBidWcgd2hlcmUgbmV3IGJvZHkgb3IgbmV3IGhlYWQgYXJlIGNyZWF0ZWQgaW4gdGhlIGRvbVxuICAvLyBpZiB5b3Ugc2V0IGhlYWQub3V0ZXJIVE1MLCBhIG5ldyBib2R5IHRhZyBpcyBhcHBlbmRlZCwgc28gdGhlIGRvbSBnZXQgMiBib2R5XG4gIC8vICYgaXQgYnJlYWsgdGhlIHN3aXRjaEZhbGxiYWNrIHdoaWNoIHJlcGxhY2UgaGVhZCAmIGJvZHlcbiAgaWYgKCF0aGlzLm9wdGlvbnMuc3dpdGNoZXMuaGVhZCkge1xuICAgIHRoaXMub3B0aW9ucy5zd2l0Y2hlcy5oZWFkID0gdGhpcy5zd2l0Y2hFbGVtZW50c0FsdFxuICB9XG4gIGlmICghdGhpcy5vcHRpb25zLnN3aXRjaGVzLmJvZHkpIHtcbiAgICB0aGlzLm9wdGlvbnMuc3dpdGNoZXMuYm9keSA9IHRoaXMuc3dpdGNoRWxlbWVudHNBbHRcbiAgfVxuICBpZiAodHlwZW9mIG9wdGlvbnMuYW5hbHl0aWNzICE9PSBcImZ1bmN0aW9uXCIpIHtcbiAgICBvcHRpb25zLmFuYWx5dGljcyA9IGZ1bmN0aW9uKCkge31cbiAgfVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB0aGlzLnBhcnNlRE9NKGVsIHx8IGRvY3VtZW50KVxufVxuIiwicmVxdWlyZShcIi4uL3BvbHlmaWxscy9GdW5jdGlvbi5wcm90b3R5cGUuYmluZFwiKVxuXG52YXIgb2ZmID0gcmVxdWlyZShcIi4uL2V2ZW50cy9vZmZcIilcbnZhciBjbG9uZSA9IHJlcXVpcmUoXCIuLi9jbG9uZVwiKVxuXG52YXIgYXR0ckNsaWNrID0gXCJkYXRhLXBqYXgtY2xpY2stc3RhdGVcIlxuXG52YXIgZm9ybUFjdGlvbiA9IGZ1bmN0aW9uKGVsLCBldmVudCl7XG5cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zID0ge1xuICAgIHJlcXVlc3RVcmwgOiBlbC5nZXRBdHRyaWJ1dGUoJ2FjdGlvbicpIHx8IHdpbmRvdy5sb2NhdGlvbi5ocmVmLFxuICAgIHJlcXVlc3RNZXRob2QgOiBlbC5nZXRBdHRyaWJ1dGUoJ21ldGhvZCcpIHx8ICdHRVQnLFxuICB9XG5cbiAgLy9jcmVhdGUgYSB0ZXN0YWJsZSB2aXJ0dWFsIGxpbmsgb2YgdGhlIGZvcm0gYWN0aW9uXG4gIHZhciB2aXJ0TGlua0VsZW1lbnQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdhJyk7XG4gIHZpcnRMaW5rRWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2hyZWYnLCB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFVybCk7XG5cbiAgLy8gSWdub3JlIGV4dGVybmFsIGxpbmtzLlxuICBpZiAodmlydExpbmtFbGVtZW50LnByb3RvY29sICE9PSB3aW5kb3cubG9jYXRpb24ucHJvdG9jb2wgfHwgdmlydExpbmtFbGVtZW50Lmhvc3QgIT09IHdpbmRvdy5sb2NhdGlvbi5ob3N0KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJleHRlcm5hbFwiKTtcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBjbGljayBpZiB3ZSBhcmUgb24gYW4gYW5jaG9yIG9uIHRoZSBzYW1lIHBhZ2VcbiAgaWYgKHZpcnRMaW5rRWxlbWVudC5wYXRobmFtZSA9PT0gd2luZG93LmxvY2F0aW9uLnBhdGhuYW1lICYmIHZpcnRMaW5rRWxlbWVudC5oYXNoLmxlbmd0aCA+IDApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1wcmVzZW50XCIpO1xuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGVtcHR5IGFuY2hvciBcImZvby5odG1sI1wiXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdICsgXCIjXCIpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1lbXB0eVwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gaWYgZGVjbGFyZWQgYXMgYSBmdWxsIHJlbG9hZCwganVzdCBub3JtYWxseSBzdWJtaXQgdGhlIGZvcm1cbiAgaWYgKCB0aGlzLm9wdGlvbnMuY3VycmVudFVybEZ1bGxSZWxvYWQpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInJlbG9hZFwiKTtcbiAgICByZXR1cm47XG4gIH1cblxuICBldmVudC5wcmV2ZW50RGVmYXVsdCgpXG4gIHZhciBuYW1lTGlzdCA9IFtdO1xuICB2YXIgcGFyYW1PYmplY3QgPSBbXTtcbiAgZm9yKHZhciBlbGVtZW50S2V5IGluIGVsLmVsZW1lbnRzKSB7XG4gICAgdmFyIGVsZW1lbnQgPSBlbC5lbGVtZW50c1tlbGVtZW50S2V5XTtcbiAgICBpZiAoISFlbGVtZW50Lm5hbWUgJiYgZWxlbWVudC5hdHRyaWJ1dGVzICE9PSB1bmRlZmluZWQgJiYgZWxlbWVudC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgIT09ICdidXR0b24nKXtcbiAgICAgIGlmIChcbiAgICAgICAgKGVsZW1lbnQudHlwZSAhPT0gJ2NoZWNrYm94JyAmJiBlbGVtZW50LnR5cGUgIT09ICdyYWRpbycpIHx8IGVsZW1lbnQuY2hlY2tlZFxuICAgICAgKSB7XG4gICAgICAgIGlmKG5hbWVMaXN0LmluZGV4T2YoZWxlbWVudC5uYW1lKSA9PT0gLTEpe1xuICAgICAgICAgIG5hbWVMaXN0LnB1c2goZWxlbWVudC5uYW1lKTtcbiAgICAgICAgICBwYXJhbU9iamVjdC5wdXNoKHsgbmFtZTogZW5jb2RlVVJJQ29tcG9uZW50KGVsZW1lbnQubmFtZSksIHZhbHVlOiBlbmNvZGVVUklDb21wb25lbnQoZWxlbWVudC52YWx1ZSl9KTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfVxuXG5cblxuICAvL0NyZWF0aW5nIGEgZ2V0U3RyaW5nXG4gIHZhciBwYXJhbXNTdHJpbmcgPSAocGFyYW1PYmplY3QubWFwKGZ1bmN0aW9uKHZhbHVlKXtyZXR1cm4gdmFsdWUubmFtZStcIj1cIit2YWx1ZS52YWx1ZTt9KSkuam9pbignJicpO1xuXG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0UGF5bG9hZCA9IHBhcmFtT2JqZWN0O1xuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgPSBwYXJhbXNTdHJpbmc7XG5cbiAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJzdWJtaXRcIik7XG5cbiAgdGhpcy5sb2FkVXJsKHZpcnRMaW5rRWxlbWVudC5ocmVmLCBjbG9uZSh0aGlzLm9wdGlvbnMpKVxuXG59O1xuXG52YXIgaXNEZWZhdWx0UHJldmVudGVkID0gZnVuY3Rpb24oZXZlbnQpIHtcbiAgcmV0dXJuIGV2ZW50LmRlZmF1bHRQcmV2ZW50ZWQgfHwgZXZlbnQucmV0dXJuVmFsdWUgPT09IGZhbHNlO1xufTtcblxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciB0aGF0ID0gdGhpc1xuXG4gIG9mZihlbCwgXCJzdWJtaXRcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgZm9ybUFjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgfSlcblxuICBvZmYoZWwsIFwia2V5dXBcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG5cbiAgICBpZiAoZXZlbnQua2V5Q29kZSA9PSAxMykge1xuICAgICAgZm9ybUFjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgICB9XG4gIH0uYmluZCh0aGlzKSlcbn1cbiIsInJlcXVpcmUoXCIuLi9wb2x5ZmlsbHMvRnVuY3Rpb24ucHJvdG90eXBlLmJpbmRcIilcblxudmFyIG9mZiA9IHJlcXVpcmUoXCIuLi9ldmVudHMvb2ZmXCIpXG52YXIgY2xvbmUgPSByZXF1aXJlKFwiLi4vY2xvbmVcIilcblxudmFyIGF0dHJDbGljayA9IFwiZGF0YS1wamF4LWNsaWNrLXN0YXRlXCJcbnZhciBhdHRyS2V5ID0gXCJkYXRhLXBqYXgta2V5dXAtc3RhdGVcIlxuXG52YXIgbGlua0FjdGlvbiA9IGZ1bmN0aW9uKGVsLCBldmVudCkge1xuICAvLyBEb27igJl0IGJyZWFrIGJyb3dzZXIgc3BlY2lhbCBiZWhhdmlvciBvbiBsaW5rcyAobGlrZSBwYWdlIGluIG5ldyB3aW5kb3cpXG4gIGlmIChldmVudC53aGljaCA+IDEgfHwgZXZlbnQubWV0YUtleSB8fCBldmVudC5jdHJsS2V5IHx8IGV2ZW50LnNoaWZ0S2V5IHx8IGV2ZW50LmFsdEtleSkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwibW9kaWZpZXJcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIHdlIGRvIHRlc3Qgb24gaHJlZiBub3cgdG8gcHJldmVudCB1bmV4cGVjdGVkIGJlaGF2aW9yIGlmIGZvciBzb21lIHJlYXNvblxuICAvLyB1c2VyIGhhdmUgaHJlZiB0aGF0IGNhbiBiZSBkeW5hbWljYWxseSB1cGRhdGVkXG5cbiAgLy8gSWdub3JlIGV4dGVybmFsIGxpbmtzLlxuICBpZiAoZWwucHJvdG9jb2wgIT09IHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCB8fCBlbC5ob3N0ICE9PSB3aW5kb3cubG9jYXRpb24uaG9zdCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiZXh0ZXJuYWxcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBjbGljayBpZiB3ZSBhcmUgb24gYW4gYW5jaG9yIG9uIHRoZSBzYW1lIHBhZ2VcbiAgaWYgKGVsLnBhdGhuYW1lID09PSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgJiYgZWwuaGFzaC5sZW5ndGggPiAwKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItcHJlc2VudFwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGFuY2hvcnMgb24gdGhlIHNhbWUgcGFnZSAoa2VlcCBuYXRpdmUgYmVoYXZpb3IpXG4gIGlmIChlbC5oYXNoICYmIGVsLmhyZWYucmVwbGFjZShlbC5oYXNoLCBcIlwiKSA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYucmVwbGFjZShsb2NhdGlvbi5oYXNoLCBcIlwiKSkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yXCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgZW1wdHkgYW5jaG9yIFwiZm9vLmh0bWwjXCJcbiAgaWYgKGVsLmhyZWYgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnNwbGl0KFwiI1wiKVswXSArIFwiI1wiKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3ItZW1wdHlcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIGV2ZW50LnByZXZlbnREZWZhdWx0KClcblxuICAvLyBkb27igJl0IGRvIFwibm90aGluZ1wiIGlmIHVzZXIgdHJ5IHRvIHJlbG9hZCB0aGUgcGFnZSBieSBjbGlja2luZyB0aGUgc2FtZSBsaW5rIHR3aWNlXG4gIGlmIChcbiAgICB0aGlzLm9wdGlvbnMuY3VycmVudFVybEZ1bGxSZWxvYWQgJiZcbiAgICBlbC5ocmVmID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5zcGxpdChcIiNcIilbMF1cbiAgKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJyZWxvYWRcIilcbiAgICB0aGlzLnJlbG9hZCgpXG4gICAgcmV0dXJuXG4gIH1cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zID0gdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zIHx8IHt9O1xuICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImxvYWRcIilcbiAgdGhpcy5sb2FkVXJsKGVsLmhyZWYsIGNsb25lKHRoaXMub3B0aW9ucykpXG59XG5cbnZhciBpc0RlZmF1bHRQcmV2ZW50ZWQgPSBmdW5jdGlvbihldmVudCkge1xuICByZXR1cm4gZXZlbnQuZGVmYXVsdFByZXZlbnRlZCB8fCBldmVudC5yZXR1cm5WYWx1ZSA9PT0gZmFsc2U7XG59XG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgdmFyIHRoYXQgPSB0aGlzXG5cbiAgb2ZmKGVsLCBcImNsaWNrXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIGxpbmtBY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gIH0pXG5cbiAgb2ZmKGVsLCBcImtleXVwXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIC8vIERvbuKAmXQgYnJlYWsgYnJvd3NlciBzcGVjaWFsIGJlaGF2aW9yIG9uIGxpbmtzIChsaWtlIHBhZ2UgaW4gbmV3IHdpbmRvdylcbiAgICBpZiAoZXZlbnQud2hpY2ggPiAxIHx8IGV2ZW50Lm1ldGFLZXkgfHwgZXZlbnQuY3RybEtleSB8fCBldmVudC5zaGlmdEtleSB8fCBldmVudC5hbHRLZXkpIHtcbiAgICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyS2V5LCBcIm1vZGlmaWVyXCIpXG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBpZiAoZXZlbnQua2V5Q29kZSA9PSAxMykge1xuICAgICAgbGlua0FjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgICB9XG4gIH0uYmluZCh0aGlzKSlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oKSB7XG4gIHdpbmRvdy5sb2NhdGlvbi5yZWxvYWQoKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihsb2NhdGlvbiwgb3B0aW9ucywgY2FsbGJhY2spIHtcbiAgb3B0aW9ucyA9IG9wdGlvbnMgfHwge307XG4gIHZhciByZXF1ZXN0TWV0aG9kID0gb3B0aW9ucy5yZXF1ZXN0TWV0aG9kIHx8IFwiR0VUXCI7XG4gIHZhciByZXF1ZXN0UGF5bG9hZCA9IG9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgfHwgbnVsbDtcbiAgdmFyIHJlcXVlc3QgPSBuZXcgWE1MSHR0cFJlcXVlc3QoKVxuXG4gIHJlcXVlc3Qub25yZWFkeXN0YXRlY2hhbmdlID0gZnVuY3Rpb24oKSB7XG4gICAgaWYgKHJlcXVlc3QucmVhZHlTdGF0ZSA9PT0gNCkge1xuICAgICAgaWYgKHJlcXVlc3Quc3RhdHVzID09PSAyMDApIHtcbiAgICAgICAgY2FsbGJhY2socmVxdWVzdC5yZXNwb25zZVRleHQsIHJlcXVlc3QpXG4gICAgICB9XG4gICAgICBlbHNlIHtcbiAgICAgICAgY2FsbGJhY2sobnVsbCwgcmVxdWVzdClcbiAgICAgIH1cbiAgICB9XG4gIH1cblxuICAvLyBBZGQgYSB0aW1lc3RhbXAgYXMgcGFydCBvZiB0aGUgcXVlcnkgc3RyaW5nIGlmIGNhY2hlIGJ1c3RpbmcgaXMgZW5hYmxlZFxuICBpZiAodGhpcy5vcHRpb25zLmNhY2hlQnVzdCkge1xuICAgIGxvY2F0aW9uICs9ICghL1s/Jl0vLnRlc3QobG9jYXRpb24pID8gXCI/XCIgOiBcIiZcIikgKyBuZXcgRGF0ZSgpLmdldFRpbWUoKVxuICB9XG5cbiAgcmVxdWVzdC5vcGVuKHJlcXVlc3RNZXRob2QudG9VcHBlckNhc2UoKSwgbG9jYXRpb24sIHRydWUpXG4gIHJlcXVlc3Quc2V0UmVxdWVzdEhlYWRlcihcIlgtUmVxdWVzdGVkLVdpdGhcIiwgXCJYTUxIdHRwUmVxdWVzdFwiKVxuXG4gIC8vIEFkZCB0aGUgcmVxdWVzdCBwYXlsb2FkIGlmIGF2YWlsYWJsZVxuICBpZiAob3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyAhPSB1bmRlZmluZWQgJiYgb3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyAhPSBcIlwiKSB7XG4gICAgLy8gU2VuZCB0aGUgcHJvcGVyIGhlYWRlciBpbmZvcm1hdGlvbiBhbG9uZyB3aXRoIHRoZSByZXF1ZXN0XG4gICAgcmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKFwiQ29udGVudC10eXBlXCIsIFwiYXBwbGljYXRpb24veC13d3ctZm9ybS11cmxlbmNvZGVkXCIpO1xuICB9XG5cbiAgcmVxdWVzdC5zZW5kKHJlcXVlc3RQYXlsb2FkKVxuXG4gIHJldHVybiByZXF1ZXN0XG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2ZvcmVhY2gtZWxzXCIpXG5cbnZhciBkZWZhdWx0U3dpdGNoZXMgPSByZXF1aXJlKFwiLi9zd2l0Y2hlc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKHN3aXRjaGVzLCBzd2l0Y2hlc09wdGlvbnMsIHNlbGVjdG9ycywgZnJvbUVsLCB0b0VsLCBvcHRpb25zKSB7XG4gIHNlbGVjdG9ycy5mb3JFYWNoKGZ1bmN0aW9uKHNlbGVjdG9yKSB7XG4gICAgdmFyIG5ld0VscyA9IGZyb21FbC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKVxuICAgIHZhciBvbGRFbHMgPSB0b0VsLnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpXG4gICAgaWYgKHRoaXMubG9nKSB7XG4gICAgICB0aGlzLmxvZyhcIlBqYXggc3dpdGNoXCIsIHNlbGVjdG9yLCBuZXdFbHMsIG9sZEVscylcbiAgICB9XG4gICAgaWYgKG5ld0Vscy5sZW5ndGggIT09IG9sZEVscy5sZW5ndGgpIHtcbiAgICAgIC8vIGZvckVhY2hFbHMobmV3RWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgLy8gICB0aGlzLmxvZyhcIm5ld0VsXCIsIGVsLCBlbC5vdXRlckhUTUwpXG4gICAgICAvLyB9LCB0aGlzKVxuICAgICAgLy8gZm9yRWFjaEVscyhvbGRFbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICAvLyAgIHRoaXMubG9nKFwib2xkRWxcIiwgZWwsIGVsLm91dGVySFRNTClcbiAgICAgIC8vIH0sIHRoaXMpXG4gICAgICB0aHJvdyBcIkRPTSBkb2VzbuKAmXQgbG9vayB0aGUgc2FtZSBvbiBuZXcgbG9hZGVkIHBhZ2U6IOKAmVwiICsgc2VsZWN0b3IgKyBcIuKAmSAtIG5ldyBcIiArIG5ld0Vscy5sZW5ndGggKyBcIiwgb2xkIFwiICsgb2xkRWxzLmxlbmd0aFxuICAgIH1cblxuICAgIGZvckVhY2hFbHMobmV3RWxzLCBmdW5jdGlvbihuZXdFbCwgaSkge1xuICAgICAgdmFyIG9sZEVsID0gb2xkRWxzW2ldXG4gICAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgICAgdGhpcy5sb2coXCJuZXdFbFwiLCBuZXdFbCwgXCJvbGRFbFwiLCBvbGRFbClcbiAgICAgIH1cbiAgICAgIGlmIChzd2l0Y2hlc1tzZWxlY3Rvcl0pIHtcbiAgICAgICAgc3dpdGNoZXNbc2VsZWN0b3JdLmJpbmQodGhpcykob2xkRWwsIG5ld0VsLCBvcHRpb25zLCBzd2l0Y2hlc09wdGlvbnNbc2VsZWN0b3JdKVxuICAgICAgfVxuICAgICAgZWxzZSB7XG4gICAgICAgIGRlZmF1bHRTd2l0Y2hlcy5vdXRlckhUTUwuYmluZCh0aGlzKShvbGRFbCwgbmV3RWwsIG9wdGlvbnMpXG4gICAgICB9XG4gICAgfSwgdGhpcylcbiAgfSwgdGhpcylcbn1cbiIsInZhciBvbiA9IHJlcXVpcmUoXCIuL2V2ZW50cy9vbi5qc1wiKVxuLy8gdmFyIG9mZiA9IHJlcXVpcmUoXCIuL2xpYi9ldmVudHMvb24uanNcIilcbi8vIHZhciB0cmlnZ2VyID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy90cmlnZ2VyLmpzXCIpXG5cblxubW9kdWxlLmV4cG9ydHMgPSB7XG4gIG91dGVySFRNTDogZnVuY3Rpb24ob2xkRWwsIG5ld0VsKSB7XG4gICAgb2xkRWwub3V0ZXJIVE1MID0gbmV3RWwub3V0ZXJIVE1MXG4gICAgdGhpcy5vblN3aXRjaCgpXG4gIH0sXG5cbiAgaW5uZXJIVE1MOiBmdW5jdGlvbihvbGRFbCwgbmV3RWwpIHtcbiAgICBvbGRFbC5pbm5lckhUTUwgPSBuZXdFbC5pbm5lckhUTUxcbiAgICBvbGRFbC5jbGFzc05hbWUgPSBuZXdFbC5jbGFzc05hbWVcbiAgICB0aGlzLm9uU3dpdGNoKClcbiAgfSxcblxuICBzaWRlQnlTaWRlOiBmdW5jdGlvbihvbGRFbCwgbmV3RWwsIG9wdGlvbnMsIHN3aXRjaE9wdGlvbnMpIHtcbiAgICB2YXIgZm9yRWFjaCA9IEFycmF5LnByb3RvdHlwZS5mb3JFYWNoXG4gICAgdmFyIGVsc1RvUmVtb3ZlID0gW11cbiAgICB2YXIgZWxzVG9BZGQgPSBbXVxuICAgIHZhciBmcmFnVG9BcHBlbmQgPSBkb2N1bWVudC5jcmVhdGVEb2N1bWVudEZyYWdtZW50KClcbiAgICAvLyBoZWlnaHQgdHJhbnNpdGlvbiBhcmUgc2hpdHR5IG9uIHNhZmFyaVxuICAgIC8vIHNvIGNvbW1lbnRlZCBmb3Igbm93ICh1bnRpbCBJIGZvdW5kIHNvbWV0aGluZyA/KVxuICAgIC8vIHZhciByZWxldmFudEhlaWdodCA9IDBcbiAgICB2YXIgYW5pbWF0aW9uRXZlbnROYW1lcyA9IFwiYW5pbWF0aW9uZW5kIHdlYmtpdEFuaW1hdGlvbkVuZCBNU0FuaW1hdGlvbkVuZCBvYW5pbWF0aW9uZW5kXCJcbiAgICB2YXIgYW5pbWF0ZWRFbHNOdW1iZXIgPSAwXG4gICAgdmFyIHNleHlBbmltYXRpb25FbmQgPSBmdW5jdGlvbihlKSB7XG4gICAgICAgICAgaWYgKGUudGFyZ2V0ICE9IGUuY3VycmVudFRhcmdldCkge1xuICAgICAgICAgICAgLy8gZW5kIHRyaWdnZXJlZCBieSBhbiBhbmltYXRpb24gb24gYSBjaGlsZFxuICAgICAgICAgICAgcmV0dXJuXG4gICAgICAgICAgfVxuXG4gICAgICAgICAgYW5pbWF0ZWRFbHNOdW1iZXItLVxuICAgICAgICAgIGlmIChhbmltYXRlZEVsc051bWJlciA8PSAwICYmIGVsc1RvUmVtb3ZlKSB7XG4gICAgICAgICAgICBlbHNUb1JlbW92ZS5mb3JFYWNoKGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgICAgICAgIC8vIGJyb3dzaW5nIHF1aWNrbHkgY2FuIG1ha2UgdGhlIGVsXG4gICAgICAgICAgICAgIC8vIGFscmVhZHkgcmVtb3ZlZCBieSBsYXN0IHBhZ2UgdXBkYXRlID9cbiAgICAgICAgICAgICAgaWYgKGVsLnBhcmVudE5vZGUpIHtcbiAgICAgICAgICAgICAgICBlbC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKGVsKVxuICAgICAgICAgICAgICB9XG4gICAgICAgICAgICB9KVxuXG4gICAgICAgICAgICBlbHNUb0FkZC5mb3JFYWNoKGZ1bmN0aW9uKGVsKSB7XG4gICAgICAgICAgICAgIGVsLmNsYXNzTmFtZSA9IGVsLmNsYXNzTmFtZS5yZXBsYWNlKGVsLmdldEF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpLCBcIlwiKVxuICAgICAgICAgICAgICBlbC5yZW1vdmVBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKVxuICAgICAgICAgICAgICAvLyBQamF4Lm9mZihlbCwgYW5pbWF0aW9uRXZlbnROYW1lcywgc2V4eUFuaW1hdGlvbkVuZCwgdHJ1ZSlcbiAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgIGVsc1RvQWRkID0gbnVsbCAvLyBmcmVlIG1lbW9yeVxuICAgICAgICAgICAgZWxzVG9SZW1vdmUgPSBudWxsIC8vIGZyZWUgbWVtb3J5XG5cbiAgICAgICAgICAgIC8vIGFzc3VtZSB0aGUgaGVpZ2h0IGlzIG5vdyB1c2VsZXNzIChhdm9pZCBidWcgc2luY2UgdGhlcmUgaXMgb3ZlcmZsb3cgaGlkZGVuIG9uIHRoZSBwYXJlbnQpXG4gICAgICAgICAgICAvLyBvbGRFbC5zdHlsZS5oZWlnaHQgPSBcImF1dG9cIlxuXG4gICAgICAgICAgICAvLyB0aGlzIGlzIHRvIHRyaWdnZXIgc29tZSByZXBhaW50IChleGFtcGxlOiBwaWN0dXJlZmlsbClcbiAgICAgICAgICAgIHRoaXMub25Td2l0Y2goKVxuICAgICAgICAgICAgLy8gUGpheC50cmlnZ2VyKHdpbmRvdywgXCJzY3JvbGxcIilcbiAgICAgICAgICB9XG4gICAgICAgIH0uYmluZCh0aGlzKVxuXG4gICAgLy8gRm9yY2UgaGVpZ2h0IHRvIGJlIGFibGUgdG8gdHJpZ2dlciBjc3MgYW5pbWF0aW9uXG4gICAgLy8gaGVyZSB3ZSBnZXQgdGhlIHJlbGV2YW50IGhlaWdodFxuICAgIC8vIG9sZEVsLnBhcmVudE5vZGUuYXBwZW5kQ2hpbGQobmV3RWwpXG4gICAgLy8gcmVsZXZhbnRIZWlnaHQgPSBuZXdFbC5nZXRCb3VuZGluZ0NsaWVudFJlY3QoKS5oZWlnaHRcbiAgICAvLyBvbGRFbC5wYXJlbnROb2RlLnJlbW92ZUNoaWxkKG5ld0VsKVxuICAgIC8vIG9sZEVsLnN0eWxlLmhlaWdodCA9IG9sZEVsLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpLmhlaWdodCArIFwicHhcIlxuXG4gICAgc3dpdGNoT3B0aW9ucyA9IHN3aXRjaE9wdGlvbnMgfHwge31cblxuICAgIGZvckVhY2guY2FsbChvbGRFbC5jaGlsZE5vZGVzLCBmdW5jdGlvbihlbCkge1xuICAgICAgZWxzVG9SZW1vdmUucHVzaChlbClcbiAgICAgIGlmIChlbC5jbGFzc0xpc3QgJiYgIWVsLmNsYXNzTGlzdC5jb250YWlucyhcImpzLVBqYXgtcmVtb3ZlXCIpKSB7XG4gICAgICAgIC8vIGZvciBmYXN0IHN3aXRjaCwgY2xlYW4gZWxlbWVudCB0aGF0IGp1c3QgaGF2ZSBiZWVuIGFkZGVkLCAmIG5vdCBjbGVhbmVkIHlldC5cbiAgICAgICAgaWYgKGVsLmhhc0F0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpKSB7XG4gICAgICAgICAgZWwuY2xhc3NOYW1lID0gZWwuY2xhc3NOYW1lLnJlcGxhY2UoZWwuZ2V0QXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIiksIFwiXCIpXG4gICAgICAgICAgZWwucmVtb3ZlQXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIilcbiAgICAgICAgfVxuICAgICAgICBlbC5jbGFzc0xpc3QuYWRkKFwianMtUGpheC1yZW1vdmVcIilcbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzICYmIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLnJlbW92ZUVsZW1lbnQpIHtcbiAgICAgICAgICBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5yZW1vdmVFbGVtZW50KGVsKVxuICAgICAgICB9XG4gICAgICAgIGlmIChzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMpIHtcbiAgICAgICAgICBlbC5jbGFzc05hbWUgKz0gXCIgXCIgKyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMucmVtb3ZlICsgXCIgXCIgKyAob3B0aW9ucy5iYWNrd2FyZCA/IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5iYWNrd2FyZCA6IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5mb3J3YXJkKVxuICAgICAgICB9XG4gICAgICAgIGFuaW1hdGVkRWxzTnVtYmVyKytcbiAgICAgICAgb24oZWwsIGFuaW1hdGlvbkV2ZW50TmFtZXMsIHNleHlBbmltYXRpb25FbmQsIHRydWUpXG4gICAgICB9XG4gICAgfSlcblxuICAgIGZvckVhY2guY2FsbChuZXdFbC5jaGlsZE5vZGVzLCBmdW5jdGlvbihlbCkge1xuICAgICAgaWYgKGVsLmNsYXNzTGlzdCkge1xuICAgICAgICB2YXIgYWRkQ2xhc3NlcyA9IFwiXCJcbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcykge1xuICAgICAgICAgIGFkZENsYXNzZXMgPSBcIiBqcy1QamF4LWFkZCBcIiArIHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5hZGQgKyBcIiBcIiArIChvcHRpb25zLmJhY2t3YXJkID8gc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmZvcndhcmQgOiBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuYmFja3dhcmQpXG4gICAgICAgIH1cbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzICYmIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLmFkZEVsZW1lbnQpIHtcbiAgICAgICAgICBzd2l0Y2hPcHRpb25zLmNhbGxiYWNrcy5hZGRFbGVtZW50KGVsKVxuICAgICAgICB9XG4gICAgICAgIGVsLmNsYXNzTmFtZSArPSBhZGRDbGFzc2VzXG4gICAgICAgIGVsLnNldEF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIsIGFkZENsYXNzZXMpXG4gICAgICAgIGVsc1RvQWRkLnB1c2goZWwpXG4gICAgICAgIGZyYWdUb0FwcGVuZC5hcHBlbmRDaGlsZChlbClcbiAgICAgICAgYW5pbWF0ZWRFbHNOdW1iZXIrK1xuICAgICAgICBvbihlbCwgYW5pbWF0aW9uRXZlbnROYW1lcywgc2V4eUFuaW1hdGlvbkVuZCwgdHJ1ZSlcbiAgICAgIH1cbiAgICB9KVxuXG4gICAgLy8gcGFzcyBhbGwgY2xhc3NOYW1lIG9mIHRoZSBwYXJlbnRcbiAgICBvbGRFbC5jbGFzc05hbWUgPSBuZXdFbC5jbGFzc05hbWVcbiAgICBvbGRFbC5hcHBlbmRDaGlsZChmcmFnVG9BcHBlbmQpXG5cbiAgICAvLyBvbGRFbC5zdHlsZS5oZWlnaHQgPSByZWxldmFudEhlaWdodCArIFwicHhcIlxuICB9XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IChmdW5jdGlvbigpIHtcbiAgdmFyIGNvdW50ZXIgPSAwXG4gIHJldHVybiBmdW5jdGlvbigpIHtcbiAgICB2YXIgaWQgPSAoXCJwamF4XCIgKyAobmV3IERhdGUoKS5nZXRUaW1lKCkpKSArIFwiX1wiICsgY291bnRlclxuICAgIGNvdW50ZXIrK1xuICAgIHJldHVybiBpZFxuICB9XG59KSgpXG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2ZvcmVhY2gtZWxzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxlbWVudHMsIG9sZEVsZW1lbnRzKSB7XG4gICB0aGlzLmxvZyhcInN0eWxlaGVldHMgb2xkIGVsZW1lbnRzXCIsIG9sZEVsZW1lbnRzKTtcbiAgIHRoaXMubG9nKFwic3R5bGVoZWV0cyBuZXcgZWxlbWVudHNcIiwgZWxlbWVudHMpO1xuICB2YXIgdG9BcnJheSA9IGZ1bmN0aW9uKGVudW1lcmFibGUpe1xuICAgICAgdmFyIGFyciA9IFtdO1xuICAgICAgZm9yKHZhciBpID0gZW51bWVyYWJsZS5sZW5ndGg7IGktLTsgYXJyLnVuc2hpZnQoZW51bWVyYWJsZVtpXSkpO1xuICAgICAgcmV0dXJuIGFycjtcbiAgfTtcbiAgZm9yRWFjaEVscyhlbGVtZW50cywgZnVuY3Rpb24obmV3RWwsIGkpIHtcbiAgICB2YXIgb2xkRWxlbWVudHNBcnJheSA9IHRvQXJyYXkob2xkRWxlbWVudHMpO1xuICAgIHZhciByZXNlbWJsaW5nT2xkID0gb2xkRWxlbWVudHNBcnJheS5yZWR1Y2UoZnVuY3Rpb24oYWNjLCBvbGRFbCl7XG4gICAgICBhY2MgPSAoKG9sZEVsLmhyZWYgPT09IG5ld0VsLmhyZWYpID8gb2xkRWwgOiBhY2MpO1xuICAgICAgcmV0dXJuIGFjYztcbiAgICB9LCBudWxsKTtcblxuICAgIGlmKHJlc2VtYmxpbmdPbGQgIT09IG51bGwpe1xuICAgICAgaWYgKHRoaXMubG9nKSB7XG4gICAgICAgIHRoaXMubG9nKFwib2xkIHN0eWxlc2hlZXQgZm91bmQgbm90IHJlc2V0dGluZ1wiKTtcbiAgICAgIH1cbiAgICB9IGVsc2Uge1xuICAgICAgaWYgKHRoaXMubG9nKSB7XG4gICAgICAgIHRoaXMubG9nKFwibmV3IHN0eWxlc2hlZXQgPT4gYWRkIHRvIGhlYWRcIik7XG4gICAgICB9XG4gICAgICB2YXIgaGVhZCA9IGRvY3VtZW50LmdldEVsZW1lbnRzQnlUYWdOYW1lKCAnaGVhZCcgKVswXSxcbiAgICAgICBsaW5rID0gZG9jdW1lbnQuY3JlYXRlRWxlbWVudCggJ2xpbmsnICk7XG4gICAgICAgIGxpbmsuc2V0QXR0cmlidXRlKCAnaHJlZicsIG5ld0VsLmhyZWYgKTtcbiAgICAgICAgbGluay5zZXRBdHRyaWJ1dGUoICdyZWwnLCAnc3R5bGVzaGVldCcgKTtcbiAgICAgICAgbGluay5zZXRBdHRyaWJ1dGUoICd0eXBlJywgJ3RleHQvY3NzJyApO1xuICAgICAgICBoZWFkLmFwcGVuZENoaWxkKGxpbmspO1xuICAgIH1cbiAgfSwgdGhpcyk7XG5cbn1cbiJdfQ==
