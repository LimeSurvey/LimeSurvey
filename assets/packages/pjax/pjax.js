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
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbIi4uLy4uL25vZGUvbGliL25vZGVfbW9kdWxlcy9icm93c2VyaWZ5L25vZGVfbW9kdWxlcy9icm93c2VyLXBhY2svX3ByZWx1ZGUuanMiLCJpbmRleC5qcyIsImxpYi9jbG9uZS5qcyIsImxpYi9ldmFsLXNjcmlwdC5qcyIsImxpYi9ldmVudHMvb2ZmLmpzIiwibGliL2V2ZW50cy9vbi5qcyIsImxpYi9ldmVudHMvdHJpZ2dlci5qcyIsImxpYi9leGVjdXRlLXNjcmlwdHMuanMiLCJsaWIvZm9yZWFjaC1lbHMuanMiLCJsaWIvZm9yZWFjaC1zZWxlY3RvcnMuanMiLCJsaWIvaXMtc3VwcG9ydGVkLmpzIiwibGliL3BvbHlmaWxscy9GdW5jdGlvbi5wcm90b3R5cGUuYmluZC5qcyIsImxpYi9wcm90by9hdHRhY2gtZm9ybS5qcyIsImxpYi9wcm90by9hdHRhY2gtbGluay5qcyIsImxpYi9wcm90by9nZXQtZWxlbWVudHMuanMiLCJsaWIvcHJvdG8vbG9nLmpzIiwibGliL3Byb3RvL3BhcnNlLWRvbS11bmxvYWQuanMiLCJsaWIvcHJvdG8vcGFyc2UtZG9tLmpzIiwibGliL3Byb3RvL3BhcnNlLWVsZW1lbnQtdW5sb2FkLmpzIiwibGliL3Byb3RvL3BhcnNlLWVsZW1lbnQuanMiLCJsaWIvcHJvdG8vcGFyc2Utb3B0aW9ucy5qcyIsImxpYi9wcm90by9yZWZyZXNoLmpzIiwibGliL3Byb3RvL3VuYXR0YWNoLWZvcm0uanMiLCJsaWIvcHJvdG8vdW5hdHRhY2gtbGluay5qcyIsImxpYi9yZWxvYWQuanMiLCJsaWIvcmVxdWVzdC5qcyIsImxpYi9zd2l0Y2hlcy1zZWxlY3RvcnMuanMiLCJsaWIvc3dpdGNoZXMuanMiLCJsaWIvdW5pcXVlaWQuanMiLCJsaWIvdXBkYXRlLXN0eWxlc2hlZXRzLmpzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBO0FDQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDblNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1pBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNsREE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNYQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQy9CQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUMzQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDVEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1JBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNSQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEdBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUN6RkE7QUFDQTtBQUNBO0FBQ0E7O0FDSEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ1hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUEE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNQQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDcEJBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNwQkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQzVDQTtBQUNBO0FBQ0E7QUFDQTs7QUNIQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ3BHQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDekZBO0FBQ0E7QUFDQTtBQUNBOztBQ0hBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTs7QUNuQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25DQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOztBQ25IQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7O0FDUkE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBIiwiZmlsZSI6ImdlbmVyYXRlZC5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzQ29udGVudCI6WyIoZnVuY3Rpb24gZSh0LG4scil7ZnVuY3Rpb24gcyhvLHUpe2lmKCFuW29dKXtpZighdFtvXSl7dmFyIGE9dHlwZW9mIHJlcXVpcmU9PVwiZnVuY3Rpb25cIiYmcmVxdWlyZTtpZighdSYmYSlyZXR1cm4gYShvLCEwKTtpZihpKXJldHVybiBpKG8sITApO3ZhciBmPW5ldyBFcnJvcihcIkNhbm5vdCBmaW5kIG1vZHVsZSAnXCIrbytcIidcIik7dGhyb3cgZi5jb2RlPVwiTU9EVUxFX05PVF9GT1VORFwiLGZ9dmFyIGw9bltvXT17ZXhwb3J0czp7fX07dFtvXVswXS5jYWxsKGwuZXhwb3J0cyxmdW5jdGlvbihlKXt2YXIgbj10W29dWzFdW2VdO3JldHVybiBzKG4/bjplKX0sbCxsLmV4cG9ydHMsZSx0LG4scil9cmV0dXJuIG5bb10uZXhwb3J0c312YXIgaT10eXBlb2YgcmVxdWlyZT09XCJmdW5jdGlvblwiJiZyZXF1aXJlO2Zvcih2YXIgbz0wO288ci5sZW5ndGg7bysrKXMocltvXSk7cmV0dXJuIHN9KSIsInZhciBjbG9uZSA9IHJlcXVpcmUoJy4vbGliL2Nsb25lLmpzJylcbnZhciBleGVjdXRlU2NyaXB0cyA9IHJlcXVpcmUoJy4vbGliL2V4ZWN1dGUtc2NyaXB0cy5qcycpXG52YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuL2xpYi9mb3JlYWNoLWVscy5qc1wiKVxudmFyIG5ld1VpZCA9IHJlcXVpcmUoXCIuL2xpYi91bmlxdWVpZC5qc1wiKVxuXG52YXIgb24gPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL29uLmpzXCIpXG4vLyB2YXIgb2ZmID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy9vbi5qc1wiKVxudmFyIHRyaWdnZXIgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL3RyaWdnZXIuanNcIilcblxuXG52YXIgUGpheCA9IGZ1bmN0aW9uKG9wdGlvbnMpIHtcbiAgICB0aGlzLmZpcnN0cnVuID0gdHJ1ZVxuXG4gICAgdmFyIHBhcnNlT3B0aW9ucyA9IHJlcXVpcmUoXCIuL2xpYi9wcm90by9wYXJzZS1vcHRpb25zLmpzXCIpO1xuICAgIHBhcnNlT3B0aW9ucy5hcHBseSh0aGlzLFtvcHRpb25zXSlcbiAgICB0aGlzLmxvZyhcIlBqYXggb3B0aW9uc1wiLCB0aGlzLm9wdGlvbnMpXG5cbiAgICB0aGlzLm1heFVpZCA9IHRoaXMubGFzdFVpZCA9IG5ld1VpZCgpXG5cbiAgICB0aGlzLnBhcnNlRE9NKGRvY3VtZW50KVxuXG4gICAgb24od2luZG93LCBcInBvcHN0YXRlXCIsIGZ1bmN0aW9uKHN0KSB7XG4gICAgICBpZiAoc3Quc3RhdGUpIHtcbiAgICAgICAgdmFyIG9wdCA9IGNsb25lKHRoaXMub3B0aW9ucylcbiAgICAgICAgb3B0LnVybCA9IHN0LnN0YXRlLnVybFxuICAgICAgICBvcHQudGl0bGUgPSBzdC5zdGF0ZS50aXRsZVxuICAgICAgICBvcHQuaGlzdG9yeSA9IGZhbHNlXG4gICAgICAgIG9wdC5yZXF1ZXN0T3B0aW9ucyA9IHt9O1xuICAgICAgICBpZiAoc3Quc3RhdGUudWlkIDwgdGhpcy5sYXN0VWlkKSB7XG4gICAgICAgICAgb3B0LmJhY2t3YXJkID0gdHJ1ZVxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgIG9wdC5mb3J3YXJkID0gdHJ1ZVxuICAgICAgICB9XG4gICAgICAgIHRoaXMubGFzdFVpZCA9IHN0LnN0YXRlLnVpZFxuXG4gICAgICAgIC8vIEB0b2RvIGltcGxlbWVudCBoaXN0b3J5IGNhY2hlIGhlcmUsIGJhc2VkIG9uIHVpZFxuICAgICAgICB0aGlzLmxvYWRVcmwoc3Quc3RhdGUudXJsLCBvcHQpXG4gICAgICB9XG4gICAgfS5iaW5kKHRoaXMpKTtcblxuICAgIHJldHVybiB0aGlzO1xuICB9XG5cblBqYXgucHJvdG90eXBlID0ge1xuICBsb2c6IHJlcXVpcmUoXCIuL2xpYi9wcm90by9sb2cuanNcIiksXG5cbiAgZ2V0RWxlbWVudHM6IHJlcXVpcmUoXCIuL2xpYi9wcm90by9nZXQtZWxlbWVudHMuanNcIiksXG5cbiAgcGFyc2VET006IHJlcXVpcmUoXCIuL2xpYi9wcm90by9wYXJzZS1kb20uanNcIiksXG5cbiAgcGFyc2VET010b1VubG9hZDogcmVxdWlyZShcIi4vbGliL3Byb3RvL3BhcnNlLWRvbS11bmxvYWQuanNcIiksXG5cbiAgcmVmcmVzaDogcmVxdWlyZShcIi4vbGliL3Byb3RvL3JlZnJlc2guanNcIiksXG5cbiAgcmVsb2FkOiByZXF1aXJlKFwiLi9saWIvcmVsb2FkLmpzXCIpLFxuXG4gIGF0dGFjaExpbms6IHJlcXVpcmUoXCIuL2xpYi9wcm90by9hdHRhY2gtbGluay5qc1wiKSxcblxuICBhdHRhY2hGb3JtOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vYXR0YWNoLWZvcm0uanNcIiksXG5cbiAgdW5hdHRhY2hMaW5rOiByZXF1aXJlKFwiLi9saWIvcHJvdG8vdW5hdHRhY2gtbGluay5qc1wiKSxcblxuICB1bmF0dGFjaEZvcm06IHJlcXVpcmUoXCIuL2xpYi9wcm90by91bmF0dGFjaC1mb3JtLmpzXCIpLFxuXG4gIHVwZGF0ZVN0eWxlc2hlZXRzOiByZXF1aXJlKFwiLi9saWIvdXBkYXRlLXN0eWxlc2hlZXRzLmpzXCIpLFxuXG4gIGZvckVhY2hTZWxlY3RvcnM6IGZ1bmN0aW9uKGNiLCBjb250ZXh0LCBET01jb250ZXh0KSB7XG4gICAgcmV0dXJuIHJlcXVpcmUoXCIuL2xpYi9mb3JlYWNoLXNlbGVjdG9ycy5qc1wiKS5iaW5kKHRoaXMpKHRoaXMub3B0aW9ucy5zZWxlY3RvcnMsIGNiLCBjb250ZXh0LCBET01jb250ZXh0KVxuICB9LFxuXG4gIHN3aXRjaFNlbGVjdG9yczogZnVuY3Rpb24oc2VsZWN0b3JzLCBmcm9tRWwsIHRvRWwsIG9wdGlvbnMpIHtcbiAgICByZXR1cm4gcmVxdWlyZShcIi4vbGliL3N3aXRjaGVzLXNlbGVjdG9ycy5qc1wiKS5iaW5kKHRoaXMpKHRoaXMub3B0aW9ucy5zd2l0Y2hlcywgdGhpcy5vcHRpb25zLnN3aXRjaGVzT3B0aW9ucywgc2VsZWN0b3JzLCBmcm9tRWwsIHRvRWwsIG9wdGlvbnMpXG4gIH0sXG5cbiAgLy8gdG9vIG11Y2ggcHJvYmxlbSB3aXRoIHRoZSBjb2RlIGJlbG93XG4gIC8vICsgaXTigJlzIHRvbyBkYW5nZXJvdXNcbi8vICAgc3dpdGNoRmFsbGJhY2s6IGZ1bmN0aW9uKGZyb21FbCwgdG9FbCkge1xuLy8gICAgIHRoaXMuc3dpdGNoU2VsZWN0b3JzKFtcImhlYWRcIiwgXCJib2R5XCJdLCBmcm9tRWwsIHRvRWwpXG4vLyAgICAgLy8gZXhlY3V0ZSBzY3JpcHQgd2hlbiBET00gaXMgbGlrZSBpdCBzaG91bGQgYmVcbi8vICAgICBQamF4LmV4ZWN1dGVTY3JpcHRzKGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoXCJoZWFkXCIpKVxuLy8gICAgIFBqYXguZXhlY3V0ZVNjcmlwdHMoZG9jdW1lbnQucXVlcnlTZWxlY3RvcihcImJvZHlcIikpXG4vLyAgIH1cblxuICBsYXRlc3RDaGFuY2U6IGZ1bmN0aW9uKGhyZWYpIHtcbiAgICB3aW5kb3cubG9jYXRpb24gPSBocmVmXG4gIH0sXG5cbiAgb25Td2l0Y2g6IGZ1bmN0aW9uKCkge1xuICAgIHRyaWdnZXIod2luZG93LCBcInJlc2l6ZSBzY3JvbGxcIilcbiAgfSxcblxuICBsb2FkQ29udGVudDogZnVuY3Rpb24oaHRtbCwgb3B0aW9ucykge1xuICAgIHZhciB0bXBFbCA9IGRvY3VtZW50LmltcGxlbWVudGF0aW9uLmNyZWF0ZUhUTUxEb2N1bWVudChcInBqYXhcIilcbiAgICB2YXIgY29sbGVjdEZvclNjcmlwdGNvbXBsZXRlID0gW1xuICAgICAgKFByb21pc2UucmVzb2x2ZShcImJhc2ljIHJlc29sdmVcIikpXG4gICAgXTtcblxuICAgIC8vIHBhcnNlIEhUTUwgYXR0cmlidXRlcyB0byBjb3B5IHRoZW1cbiAgICAvLyBzaW5jZSB3ZSBhcmUgZm9yY2VkIHRvIHVzZSBkb2N1bWVudEVsZW1lbnQuaW5uZXJIVE1MIChvdXRlckhUTUwgY2FuJ3QgYmUgdXNlZCBmb3IgPGh0bWw+KVxuICAgIHZhciBodG1sUmVnZXggPSAvPGh0bWxbXj5dKz4vZ2lcbiAgICB2YXIgaHRtbEF0dHJpYnNSZWdleCA9IC9cXHM/W2EtejpdKyg/OlxcPSg/OlxcJ3xcXFwiKVteXFwnXFxcIj5dKyg/OlxcJ3xcXFwiKSkqL2dpXG4gICAgdmFyIG1hdGNoZXMgPSBodG1sLm1hdGNoKGh0bWxSZWdleClcbiAgICBpZiAobWF0Y2hlcyAmJiBtYXRjaGVzLmxlbmd0aCkge1xuICAgICAgbWF0Y2hlcyA9IG1hdGNoZXNbMF0ubWF0Y2goaHRtbEF0dHJpYnNSZWdleClcbiAgICAgIGlmIChtYXRjaGVzLmxlbmd0aCkge1xuICAgICAgICBtYXRjaGVzLnNoaWZ0KClcbiAgICAgICAgbWF0Y2hlcy5mb3JFYWNoKGZ1bmN0aW9uKGh0bWxBdHRyaWIpIHtcbiAgICAgICAgICB2YXIgYXR0ciA9IGh0bWxBdHRyaWIudHJpbSgpLnNwbGl0KFwiPVwiKVxuICAgICAgICAgIGlmIChhdHRyLmxlbmd0aCA9PT0gMSkge1xuICAgICAgICAgICAgdG1wRWwuZG9jdW1lbnRFbGVtZW50LnNldEF0dHJpYnV0ZShhdHRyWzBdLCB0cnVlKVxuICAgICAgICAgIH1cbiAgICAgICAgICBlbHNlIHtcbiAgICAgICAgICAgIHRtcEVsLmRvY3VtZW50RWxlbWVudC5zZXRBdHRyaWJ1dGUoYXR0clswXSwgYXR0clsxXS5zbGljZSgxLCAtMSkpXG4gICAgICAgICAgfVxuICAgICAgICB9KVxuICAgICAgfVxuICAgIH1cblxuICAgIHRtcEVsLmRvY3VtZW50RWxlbWVudC5pbm5lckhUTUwgPSBodG1sXG4gICAgdGhpcy5sb2coXCJsb2FkIGNvbnRlbnRcIiwgdG1wRWwuZG9jdW1lbnRFbGVtZW50LmF0dHJpYnV0ZXMsIHRtcEVsLmRvY3VtZW50RWxlbWVudC5pbm5lckhUTUwubGVuZ3RoKVxuXG4gICAgLy8gQ2xlYXIgb3V0IGFueSBmb2N1c2VkIGNvbnRyb2xzIGJlZm9yZSBpbnNlcnRpbmcgbmV3IHBhZ2UgY29udGVudHMuXG4gICAgLy8gd2UgY2xlYXIgZm9jdXMgb24gbm9uIGZvcm0gZWxlbWVudHNcbiAgICBpZiAoZG9jdW1lbnQuYWN0aXZlRWxlbWVudCAmJiAhZG9jdW1lbnQuYWN0aXZlRWxlbWVudC52YWx1ZSkge1xuICAgICAgdHJ5IHtcbiAgICAgICAgZG9jdW1lbnQuYWN0aXZlRWxlbWVudC5ibHVyKClcbiAgICAgIH0gY2F0Y2ggKGUpIHsgfVxuICAgIH1cblxuICAgIHRoaXMuc3dpdGNoU2VsZWN0b3JzKHRoaXMub3B0aW9ucy5zZWxlY3RvcnMsIHRtcEVsLCBkb2N1bWVudCwgb3B0aW9ucylcblxuICAgIC8vcmVzZXQgc3R5bGVzaGVldHMgaWYgYWN0aXZhdGVkXG4gICAgaWYodGhpcy5vcHRpb25zLnJlUmVuZGVyQ1NTID09PSB0cnVlKXtcbiAgICAgIHRoaXMudXBkYXRlU3R5bGVzaGVldHMuY2FsbCh0aGlzLCB0bXBFbC5xdWVyeVNlbGVjdG9yQWxsKCdsaW5rW3JlbD1zdHlsZXNoZWV0XScpLCBkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKCdsaW5rW3JlbD1zdHlsZXNoZWV0XScpKTtcbiAgICB9XG5cbiAgICAvLyBGRiBidWc6IFdvbuKAmXQgYXV0b2ZvY3VzIGZpZWxkcyB0aGF0IGFyZSBpbnNlcnRlZCB2aWEgSlMuXG4gICAgLy8gVGhpcyBiZWhhdmlvciBpcyBpbmNvcnJlY3QuIFNvIGlmIHRoZXJlcyBubyBjdXJyZW50IGZvY3VzLCBhdXRvZm9jdXNcbiAgICAvLyB0aGUgbGFzdCBmaWVsZC5cbiAgICAvL1xuICAgIC8vIGh0dHA6Ly93d3cudzMub3JnL2h0bWwvd2cvZHJhZnRzL2h0bWwvbWFzdGVyL2Zvcm1zLmh0bWxcbiAgICB2YXIgYXV0b2ZvY3VzRWwgPSBBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKFwiW2F1dG9mb2N1c11cIikpLnBvcCgpXG4gICAgaWYgKGF1dG9mb2N1c0VsICYmIGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQgIT09IGF1dG9mb2N1c0VsKSB7XG4gICAgICBhdXRvZm9jdXNFbC5mb2N1cygpO1xuICAgIH1cblxuICAgIC8vIGV4ZWN1dGUgc2NyaXB0cyB3aGVuIERPTSBoYXZlIGJlZW4gY29tcGxldGVseSB1cGRhdGVkXG4gICAgdGhpcy5vcHRpb25zLnNlbGVjdG9ycy5mb3JFYWNoKCBmdW5jdGlvbihzZWxlY3Rvcikge1xuICAgICAgZm9yRWFjaEVscyhkb2N1bWVudC5xdWVyeVNlbGVjdG9yQWxsKHNlbGVjdG9yKSwgZnVuY3Rpb24oZWwpIHtcblxuICAgICAgICBjb2xsZWN0Rm9yU2NyaXB0Y29tcGxldGUucHVzaC5hcHBseShjb2xsZWN0Rm9yU2NyaXB0Y29tcGxldGUsIGV4ZWN1dGVTY3JpcHRzLmNhbGwodGhpcywgZWwpKTtcblxuICAgICAgfSwgdGhpcyk7XG5cbiAgICB9LHRoaXMpO1xuICAgIC8vIH1cbiAgICAvLyBjYXRjaChlKSB7XG4gICAgLy8gICBpZiAodGhpcy5vcHRpb25zLmRlYnVnKSB7XG4gICAgLy8gICAgIHRoaXMubG9nKFwiUGpheCBzd2l0Y2ggZmFpbDogXCIsIGUpXG4gICAgLy8gICB9XG4gICAgLy8gICB0aGlzLnN3aXRjaEZhbGxiYWNrKHRtcEVsLCBkb2N1bWVudClcbiAgICAvLyB9XG4gICAgdGhpcy5sb2coXCJ3YWl0aW5nIGZvciBzY3JpcHRjb21wbGV0ZVwiLGNvbGxlY3RGb3JTY3JpcHRjb21wbGV0ZSk7XG5cbiAgICAvL0ZhbGxiYWNrISBJZiBzb21ldGhpbmcgY2FuJ3QgYmUgbG9hZGVkIG9yIGlzIG5vdCBsb2FkZWQgY29ycmVjdGx5IC0+IGp1c3QgZm9yY2UgZXZlbnRpbmcgaW4gZXJyb3JcbiAgICB2YXIgdGltZU91dFNjcmlwdEV2ZW50ID0gbnVsbDtcbiAgICB0aW1lT3V0U2NyaXB0RXZlbnQgPSB3aW5kb3cuc2V0VGltZW91dCggZnVuY3Rpb24oKXtcbiAgICAgIHRyaWdnZXIoZG9jdW1lbnQsXCJwamF4OnNjcmlwdGNvbXBsZXRlIHBqYXg6c2NyaXB0dGltZW91dFwiLCBvcHRpb25zKVxuICAgICAgdGltZU91dFNjcmlwdEV2ZW50ID0gbnVsbDtcbiAgICB9LCB0aGlzLm9wdGlvbnMuc2NyaXB0bG9hZHRpbWVvdXQpO1xuXG4gICAgUHJvbWlzZS5hbGwoY29sbGVjdEZvclNjcmlwdGNvbXBsZXRlKS50aGVuKFxuICAgICAgLy9yZXNvbHZlZFxuICAgICAgZnVuY3Rpb24oKXtcbiAgICAgICAgaWYodGltZU91dFNjcmlwdEV2ZW50ICE9PSBudWxsICl7XG4gICAgICAgICAgd2luZG93LmNsZWFyVGltZW91dCh0aW1lT3V0U2NyaXB0RXZlbnQpO1xuICAgICAgICAgIHRyaWdnZXIoZG9jdW1lbnQsXCJwamF4OnNjcmlwdGNvbXBsZXRlIHBqYXg6c2NyaXB0c3VjY2Vzc1wiLCBvcHRpb25zKVxuICAgICAgICB9XG4gICAgICB9LFxuICAgICAgZnVuY3Rpb24oKXtcbiAgICAgICAgaWYodGltZU91dFNjcmlwdEV2ZW50ICE9PSBudWxsICl7XG4gICAgICAgICAgd2luZG93LmNsZWFyVGltZW91dCh0aW1lT3V0U2NyaXB0RXZlbnQpO1xuICAgICAgICAgIHRyaWdnZXIoZG9jdW1lbnQsXCJwamF4OnNjcmlwdGNvbXBsZXRlIHBqYXg6c2NyaXB0ZXJyb3JcIiwgb3B0aW9ucylcbiAgICAgICAgfVxuICAgICAgfVxuICAgICk7XG5cblxuICB9LFxuXG4gIGRvUmVxdWVzdDogcmVxdWlyZShcIi4vbGliL3JlcXVlc3QuanNcIiksXG5cbiAgbG9hZFVybDogZnVuY3Rpb24oaHJlZiwgb3B0aW9ucykge1xuICAgIHRoaXMubG9nKFwibG9hZCBocmVmXCIsIGhyZWYsIG9wdGlvbnMpXG5cbiAgICB0cmlnZ2VyKGRvY3VtZW50LCBcInBqYXg6c2VuZFwiLCBvcHRpb25zKTtcblxuICAgIC8vIERvIHRoZSByZXF1ZXN0XG4gICAgdGhpcy5kb1JlcXVlc3QoaHJlZiwgb3B0aW9ucy5yZXF1ZXN0T3B0aW9ucywgZnVuY3Rpb24oaHRtbCkge1xuICAgICAgLy8gRmFpbCBpZiB1bmFibGUgdG8gbG9hZCBIVE1MIHZpYSBBSkFYXG4gICAgICBpZiAoaHRtbCA9PT0gZmFsc2UpIHtcbiAgICAgICAgdHJpZ2dlcihkb2N1bWVudCxcInBqYXg6Y29tcGxldGUgcGpheDplcnJvclwiLCBvcHRpb25zKVxuXG4gICAgICAgIHJldHVyblxuICAgICAgfVxuXG4gICAgICAvLyBDbGVhciBvdXQgYW55IGZvY3VzZWQgY29udHJvbHMgYmVmb3JlIGluc2VydGluZyBuZXcgcGFnZSBjb250ZW50cy5cbiAgICAgIGRvY3VtZW50LmFjdGl2ZUVsZW1lbnQuYmx1cigpXG5cbiAgICAgIHRyeSB7XG4gICAgICAgIHRoaXMubG9hZENvbnRlbnQoaHRtbCwgb3B0aW9ucylcbiAgICAgIH1cbiAgICAgIGNhdGNoIChlKSB7XG4gICAgICAgIGlmICghdGhpcy5vcHRpb25zLmRlYnVnKSB7XG4gICAgICAgICAgaWYgKGNvbnNvbGUgJiYgdGhpcy5vcHRpb25zLmxvZ09iamVjdC5lcnJvcikge1xuICAgICAgICAgICAgdGhpcy5vcHRpb25zLmxvZ09iamVjdC5lcnJvcihcIlBqYXggc3dpdGNoIGZhaWw6IFwiLCBlKVxuICAgICAgICAgIH1cbiAgICAgICAgICB0aGlzLmxhdGVzdENoYW5jZShocmVmKVxuICAgICAgICAgIHJldHVyblxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgIGlmICh0aGlzLm9wdGlvbnMuZm9yY2VSZWRpcmVjdE9uRmFpbCkge1xuICAgICAgICAgICAgdGhpcy5sYXRlc3RDaGFuY2UoaHJlZik7XG4gICAgICAgICAgfVxuICAgICAgICAgIHRocm93IGU7XG4gICAgICAgIH1cbiAgICAgIH1cblxuICAgICAgaWYgKG9wdGlvbnMuaGlzdG9yeSkge1xuICAgICAgICBpZiAodGhpcy5maXJzdHJ1bikge1xuICAgICAgICAgIHRoaXMubGFzdFVpZCA9IHRoaXMubWF4VWlkID0gbmV3VWlkKClcbiAgICAgICAgICB0aGlzLmZpcnN0cnVuID0gZmFsc2VcbiAgICAgICAgICB3aW5kb3cuaGlzdG9yeS5yZXBsYWNlU3RhdGUoe1xuICAgICAgICAgICAgdXJsOiB3aW5kb3cubG9jYXRpb24uaHJlZixcbiAgICAgICAgICAgIHRpdGxlOiBkb2N1bWVudC50aXRsZSxcbiAgICAgICAgICAgIHVpZDogdGhpcy5tYXhVaWRcbiAgICAgICAgICB9LFxuICAgICAgICAgIGRvY3VtZW50LnRpdGxlKVxuICAgICAgICB9XG5cbiAgICAgICAgLy8gVXBkYXRlIGJyb3dzZXIgaGlzdG9yeVxuICAgICAgICB0aGlzLmxhc3RVaWQgPSB0aGlzLm1heFVpZCA9IG5ld1VpZCgpXG4gICAgICAgIHdpbmRvdy5oaXN0b3J5LnB1c2hTdGF0ZSh7XG4gICAgICAgICAgdXJsOiBocmVmLFxuICAgICAgICAgIHRpdGxlOiBvcHRpb25zLnRpdGxlLFxuICAgICAgICAgIHVpZDogdGhpcy5tYXhVaWRcbiAgICAgICAgfSxcbiAgICAgICAgICBvcHRpb25zLnRpdGxlLFxuICAgICAgICAgIGhyZWYpXG4gICAgICB9XG5cbiAgICAgIHRoaXMuZm9yRWFjaFNlbGVjdG9ycyhmdW5jdGlvbihlbCkge1xuICAgICAgICB0aGlzLnBhcnNlRE9NKGVsKVxuICAgICAgfSwgdGhpcylcblxuICAgICAgLy8gRmlyZSBFdmVudHNcbiAgICAgIHRyaWdnZXIoZG9jdW1lbnQsXCJwamF4OmNvbXBsZXRlIHBqYXg6c3VjY2Vzc1wiLCBvcHRpb25zKVxuXG4gICAgICBvcHRpb25zLmFuYWx5dGljcygpXG5cbiAgICAgIC8vIFNjcm9sbCBwYWdlIHRvIHRvcCBvbiBuZXcgcGFnZSBsb2FkXG4gICAgICBpZiAob3B0aW9ucy5zY3JvbGxUbyAhPT0gZmFsc2UpIHtcbiAgICAgICAgaWYgKG9wdGlvbnMuc2Nyb2xsVG8ubGVuZ3RoID4gMSkge1xuICAgICAgICAgIHdpbmRvdy5zY3JvbGxUbyhvcHRpb25zLnNjcm9sbFRvWzBdLCBvcHRpb25zLnNjcm9sbFRvWzFdKVxuICAgICAgICB9XG4gICAgICAgIGVsc2Uge1xuICAgICAgICAgIHdpbmRvdy5zY3JvbGxUbygwLCBvcHRpb25zLnNjcm9sbFRvKVxuICAgICAgICB9XG4gICAgICB9XG4gICAgfS5iaW5kKHRoaXMpKVxuICB9XG59XG5cblBqYXguaXNTdXBwb3J0ZWQgPSByZXF1aXJlKFwiLi9saWIvaXMtc3VwcG9ydGVkLmpzXCIpO1xuXG4vL2FyZ3VhYmx5IGNvdWxkIGRvIGBpZiggcmVxdWlyZShcIi4vbGliL2lzLXN1cHBvcnRlZC5qc1wiKSgpKSB7YCBidXQgdGhhdCBtaWdodCBiZSBhIGxpdHRsZSB0byBzaW1wbGVcbmlmIChQamF4LmlzU3VwcG9ydGVkKCkpIHtcbiAgbW9kdWxlLmV4cG9ydHMgPSBQamF4XG59XG4vLyBpZiB0aGVyZSBpc27igJl0IHJlcXVpcmVkIGJyb3dzZXIgZnVuY3Rpb25zLCByZXR1cm5pbmcgc3R1cGlkIGFwaVxuZWxzZSB7XG4gIHZhciBzdHVwaWRQamF4ID0gZnVuY3Rpb24oKSB7fVxuICBmb3IgKHZhciBrZXkgaW4gUGpheC5wcm90b3R5cGUpIHtcbiAgICBpZiAoUGpheC5wcm90b3R5cGUuaGFzT3duUHJvcGVydHkoa2V5KSAmJiB0eXBlb2YgUGpheC5wcm90b3R5cGVba2V5XSA9PT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICBzdHVwaWRQamF4W2tleV0gPSBzdHVwaWRQamF4XG4gICAgfVxuICB9XG5cbiAgbW9kdWxlLmV4cG9ydHMgPSBzdHVwaWRQamF4XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKG9iaikge1xuICBpZiAobnVsbCA9PT0gb2JqIHx8IFwib2JqZWN0XCIgIT0gdHlwZW9mIG9iaikge1xuICAgIHJldHVybiBvYmpcbiAgfVxuICB2YXIgY29weSA9IG9iai5jb25zdHJ1Y3RvcigpXG4gIGZvciAodmFyIGF0dHIgaW4gb2JqKSB7XG4gICAgaWYgKG9iai5oYXNPd25Qcm9wZXJ0eShhdHRyKSkge1xuICAgICAgY29weVthdHRyXSA9IG9ialthdHRyXVxuICAgIH1cbiAgfVxuICByZXR1cm4gY29weVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB2YXIgcXVlcnlTZWxlY3RvciA9IHRoaXMub3B0aW9ucy5tYWluU2NyaXB0RWxlbWVudDtcbiAgdmFyIGNvZGUgPSAoZWwudGV4dCB8fCBlbC50ZXh0Q29udGVudCB8fCBlbC5pbm5lckhUTUwgfHwgXCJcIilcbiAgdmFyIHNyYyA9IChlbC5zcmMgfHwgXCJcIik7XG4gIHZhciBwYXJlbnQgPSBlbC5wYXJlbnROb2RlIHx8IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IocXVlcnlTZWxlY3RvcikgfHwgZG9jdW1lbnQuZG9jdW1lbnRFbGVtZW50XG4gIHZhciBzY3JpcHQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KFwic2NyaXB0XCIpXG4gIHZhciBwcm9taXNlID0gbnVsbDtcblxuICB0aGlzLmxvZyhcIkV2YWx1YXRpbmcgU2NyaXB0OiBcIiwgZWwpO1xuXG4gIGlmIChjb2RlLm1hdGNoKFwiZG9jdW1lbnQud3JpdGVcIikpIHtcbiAgICBpZiAoY29uc29sZSAmJiB0aGlzLm9wdGlvbnMubG9nT2JqZWN0LmxvZykge1xuICAgICAgdGhpcy5vcHRpb25zLmxvZ09iamVjdC5sb2coXCJTY3JpcHQgY29udGFpbnMgZG9jdW1lbnQud3JpdGUuIENhbuKAmXQgYmUgZXhlY3V0ZWQgY29ycmVjdGx5LiBDb2RlIHNraXBwZWQgXCIsIGVsKVxuICAgIH1cbiAgICByZXR1cm4gZmFsc2VcbiAgfVxuXG4gIHByb21pc2UgPSBuZXcgUHJvbWlzZSggZnVuY3Rpb24ocmVzb2x2ZSwgcmVqZWN0KXtcblxuICAgIHNjcmlwdC50eXBlID0gXCJ0ZXh0L2phdmFzY3JpcHRcIlxuICAgIGlmIChzcmMgIT0gXCJcIikge1xuICAgICAgc2NyaXB0LnNyYyA9IHNyYztcbiAgICAgIHNjcmlwdC5hZGRFdmVudExpc3RlbmVyKCdsb2FkJywgZnVuY3Rpb24oKXtyZXNvbHZlKHNyYyk7fSApO1xuICAgICAgc2NyaXB0LmFzeW5jID0gdHJ1ZTsgLy8gZm9yY2UgYXN5bmNocm9ub3VzIGxvYWRpbmcgb2YgcGVyaXBoZXJhbCBqc1xuICAgIH1cblxuICAgIGlmIChjb2RlICE9IFwiXCIpIHtcbiAgICAgIHRyeSB7XG4gICAgICAgIHNjcmlwdC5hcHBlbmRDaGlsZChkb2N1bWVudC5jcmVhdGVUZXh0Tm9kZShjb2RlKSlcbiAgICAgIH1cbiAgICAgIGNhdGNoIChlKSB7XG4gICAgICAgIC8vIG9sZCBJRXMgaGF2ZSBmdW5reSBzY3JpcHQgbm9kZXNcbiAgICAgICAgc2NyaXB0LnRleHQgPSBjb2RlXG4gICAgICB9XG4gICAgICByZXNvbHZlKCd0ZXh0LW5vZGUnKTtcbiAgICB9XG4gIH0pO1xuXG4gIHRoaXMubG9nKCdQYXJlbnRFbGVtZW50ID0+ICcsIHBhcmVudCApO1xuXG4gIC8vIGV4ZWN1dGVcbiAgcGFyZW50LmFwcGVuZENoaWxkKHNjcmlwdCk7XG4gIHBhcmVudC5yZW1vdmVDaGlsZChzY3JpcHQpXG4gIC8vIGF2b2lkIHBvbGx1dGlvbiBvbmx5IGluIGhlYWQgb3IgYm9keSB0YWdzXG4gIC8vIG9mIGlmIHRoZSBzZXR0aW5nIHJlbW92ZVNjcmlwdHNBZnRlclBhcnNpbmcgaXMgYWN0aXZlXG4gIGlmKCAoW1wiaGVhZFwiLFwiYm9keVwiXS5pbmRleE9mKCBwYXJlbnQudGFnTmFtZS50b0xvd2VyQ2FzZSgpKSA+IDApIHx8ICh0aGlzLm9wdGlvbnMucmVtb3ZlU2NyaXB0c0FmdGVyUGFyc2luZyA9PT0gdHJ1ZSkgKSB7XG4gIH1cblxuICByZXR1cm4gcHJvbWlzZTtcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4uL2ZvcmVhY2gtZWxzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWxzLCBldmVudHMsIGxpc3RlbmVyLCB1c2VDYXB0dXJlKSB7XG4gIGV2ZW50cyA9ICh0eXBlb2YgZXZlbnRzID09PSBcInN0cmluZ1wiID8gZXZlbnRzLnNwbGl0KFwiIFwiKSA6IGV2ZW50cylcblxuICBldmVudHMuZm9yRWFjaChmdW5jdGlvbihlKSB7XG4gICAgZm9yRWFjaEVscyhlbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICBlbC5yZW1vdmVFdmVudExpc3RlbmVyKGUsIGxpc3RlbmVyLCB1c2VDYXB0dXJlKVxuICAgIH0pXG4gIH0pXG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVscywgZXZlbnRzLCBsaXN0ZW5lciwgdXNlQ2FwdHVyZSkge1xuICBldmVudHMgPSAodHlwZW9mIGV2ZW50cyA9PT0gXCJzdHJpbmdcIiA/IGV2ZW50cy5zcGxpdChcIiBcIikgOiBldmVudHMpXG5cbiAgZXZlbnRzLmZvckVhY2goZnVuY3Rpb24oZSkge1xuICAgIGZvckVhY2hFbHMoZWxzLCBmdW5jdGlvbihlbCkge1xuICAgICAgZWwuYWRkRXZlbnRMaXN0ZW5lcihlLCBsaXN0ZW5lciwgdXNlQ2FwdHVyZSlcbiAgICB9KVxuICB9KVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbHMsIGV2ZW50cywgb3B0cykge1xuICBldmVudHMgPSAodHlwZW9mIGV2ZW50cyA9PT0gXCJzdHJpbmdcIiA/IGV2ZW50cy5zcGxpdChcIiBcIikgOiBldmVudHMpXG5cbiAgZXZlbnRzLmZvckVhY2goZnVuY3Rpb24oZSkge1xuICAgIHZhciBldmVudCAvLyA9IG5ldyBDdXN0b21FdmVudChlKSAvLyBkb2Vzbid0IGV2ZXJ5d2hlcmUgeWV0XG4gICAgZXZlbnQgPSBkb2N1bWVudC5jcmVhdGVFdmVudChcIkhUTUxFdmVudHNcIilcbiAgICBldmVudC5pbml0RXZlbnQoZSwgdHJ1ZSwgdHJ1ZSlcbiAgICBldmVudC5ldmVudE5hbWUgPSBlXG4gICAgaWYgKG9wdHMpIHtcbiAgICAgIE9iamVjdC5rZXlzKG9wdHMpLmZvckVhY2goZnVuY3Rpb24oa2V5KSB7XG4gICAgICAgIGV2ZW50W2tleV0gPSBvcHRzW2tleV1cbiAgICAgIH0pXG4gICAgfVxuXG4gICAgZm9yRWFjaEVscyhlbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICB2YXIgZG9tRml4ID0gZmFsc2VcbiAgICAgIGlmICghZWwucGFyZW50Tm9kZSAmJiBlbCAhPT0gZG9jdW1lbnQgJiYgZWwgIT09IHdpbmRvdykge1xuICAgICAgICAvLyBUSEFOS1MgWU9VIElFICg5LzEwLy8xMSBjb25jZXJuZWQpXG4gICAgICAgIC8vIGRpc3BhdGNoRXZlbnQgZG9lc24ndCB3b3JrIGlmIGVsZW1lbnQgaXMgbm90IGluIHRoZSBkb21cbiAgICAgICAgZG9tRml4ID0gdHJ1ZVxuICAgICAgICBkb2N1bWVudC5ib2R5LmFwcGVuZENoaWxkKGVsKVxuICAgICAgfVxuICAgICAgZWwuZGlzcGF0Y2hFdmVudChldmVudClcbiAgICAgIGlmIChkb21GaXgpIHtcbiAgICAgICAgZWwucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChlbClcbiAgICAgIH1cbiAgICB9KVxuICB9KVxufVxuIiwidmFyIGZvckVhY2hFbHMgPSByZXF1aXJlKFwiLi9mb3JlYWNoLWVsc1wiKVxudmFyIGV2YWxTY3JpcHQgPSByZXF1aXJlKFwiLi9ldmFsLXNjcmlwdFwiKVxuLy8gRmluZHMgYW5kIGV4ZWN1dGVzIHNjcmlwdHMgKHVzZWQgZm9yIG5ld2x5IGFkZGVkIGVsZW1lbnRzKVxuLy8gTmVlZGVkIHNpbmNlIGlubmVySFRNTCBkb2VzIG5vdCBydW4gc2NyaXB0c1xubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuXG4gIHRoaXMubG9nKFwiRXhlY3V0aW5nIHNjcmlwdHMgZm9yIFwiLCBlbCk7XG5cbiAgdmFyIGxvYWRpbmdTY3JpcHRzID0gW107XG5cbiAgaWYoZWwgPT09IHVuZGVmaW5lZCkgcmV0dXJuIFByb21pc2UucmVzb2x2ZSgpO1xuXG4gIGlmIChlbC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgPT09IFwic2NyaXB0XCIpIHtcbiAgICBldmFsU2NyaXB0LmNhbGwodGhpcywgZWwpO1xuICB9XG5cbiAgZm9yRWFjaEVscyhlbC5xdWVyeVNlbGVjdG9yQWxsKFwic2NyaXB0XCIpLCBmdW5jdGlvbihzY3JpcHQpIHtcbiAgICBpZiAoIXNjcmlwdC50eXBlIHx8IHNjcmlwdC50eXBlLnRvTG93ZXJDYXNlKCkgPT09IFwidGV4dC9qYXZhc2NyaXB0XCIpIHtcbiAgICAgIC8vIGlmIChzY3JpcHQucGFyZW50Tm9kZSkge1xuICAgICAgLy8gICBzY3JpcHQucGFyZW50Tm9kZS5yZW1vdmVDaGlsZChzY3JpcHQpXG4gICAgICAvLyB9XG4gICAgICBsb2FkaW5nU2NyaXB0cy5wdXNoKGV2YWxTY3JpcHQuY2FsbCh0aGlzLCBzY3JpcHQpKTtcbiAgICB9XG4gIH0sIHRoaXMpO1xuXG4gIHJldHVybiBsb2FkaW5nU2NyaXB0cztcbn1cbiIsIi8qIGdsb2JhbCBIVE1MQ29sbGVjdGlvbjogdHJ1ZSAqL1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVscywgZm4sIGNvbnRleHQpIHtcbiAgaWYgKGVscyBpbnN0YW5jZW9mIEhUTUxDb2xsZWN0aW9uIHx8IGVscyBpbnN0YW5jZW9mIE5vZGVMaXN0IHx8IGVscyBpbnN0YW5jZW9mIEFycmF5KSB7XG4gICAgcmV0dXJuIEFycmF5LnByb3RvdHlwZS5mb3JFYWNoLmNhbGwoZWxzLCBmbiwgY29udGV4dClcbiAgfVxuICAvLyBhc3N1bWUgc2ltcGxlIGRvbSBlbGVtZW50XG4gIHJldHVybiBmbi5jYWxsKGNvbnRleHQsIGVscylcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihzZWxlY3RvcnMsIGNiLCBjb250ZXh0LCBET01jb250ZXh0KSB7XG4gIERPTWNvbnRleHQgPSBET01jb250ZXh0IHx8IGRvY3VtZW50XG4gIHNlbGVjdG9ycy5mb3JFYWNoKGZ1bmN0aW9uKHNlbGVjdG9yKSB7XG4gICAgZm9yRWFjaEVscyhET01jb250ZXh0LnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpLCBjYiwgY29udGV4dClcbiAgfSlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oKSB7XG4gIC8vIEJvcnJvd2VkIHdob2xlc2FsZSBmcm9tIGh0dHBzOi8vZ2l0aHViLmNvbS9kZWZ1bmt0L2pxdWVyeS1wamF4XG4gIHJldHVybiB3aW5kb3cuaGlzdG9yeSAmJlxuICAgIHdpbmRvdy5oaXN0b3J5LnB1c2hTdGF0ZSAmJlxuICAgIHdpbmRvdy5oaXN0b3J5LnJlcGxhY2VTdGF0ZSAmJlxuICAgIC8vIHB1c2hTdGF0ZSBpc27igJl0IHJlbGlhYmxlIG9uIGlPUyB1bnRpbCA1LlxuICAgICFuYXZpZ2F0b3IudXNlckFnZW50Lm1hdGNoKC8oKGlQb2R8aVBob25lfGlQYWQpLitcXGJPU1xccytbMS00XVxcRHxXZWJBcHBzXFwvLitDRk5ldHdvcmspLylcbn1cbiIsImlmICghRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQpIHtcbiAgRnVuY3Rpb24ucHJvdG90eXBlLmJpbmQgPSBmdW5jdGlvbihvVGhpcykge1xuICAgIGlmICh0eXBlb2YgdGhpcyAhPT0gXCJmdW5jdGlvblwiKSB7XG4gICAgICAvLyBjbG9zZXN0IHRoaW5nIHBvc3NpYmxlIHRvIHRoZSBFQ01BU2NyaXB0IDUgaW50ZXJuYWwgSXNDYWxsYWJsZSBmdW5jdGlvblxuICAgICAgdGhyb3cgbmV3IFR5cGVFcnJvcihcIkZ1bmN0aW9uLnByb3RvdHlwZS5iaW5kIC0gd2hhdCBpcyB0cnlpbmcgdG8gYmUgYm91bmQgaXMgbm90IGNhbGxhYmxlXCIpXG4gICAgfVxuXG4gICAgdmFyIGFBcmdzID0gQXJyYXkucHJvdG90eXBlLnNsaWNlLmNhbGwoYXJndW1lbnRzLCAxKVxuICAgIHZhciB0aGF0ID0gdGhpc1xuICAgIHZhciBGbm9vcCA9IGZ1bmN0aW9uKCkge31cbiAgICB2YXIgZkJvdW5kID0gZnVuY3Rpb24oKSB7XG4gICAgICByZXR1cm4gdGhhdC5hcHBseSh0aGlzIGluc3RhbmNlb2YgRm5vb3AgJiYgb1RoaXMgPyB0aGlzIDogb1RoaXMsIGFBcmdzLmNvbmNhdChBcnJheS5wcm90b3R5cGUuc2xpY2UuY2FsbChhcmd1bWVudHMpKSlcbiAgICB9XG5cbiAgICBGbm9vcC5wcm90b3R5cGUgPSB0aGlzLnByb3RvdHlwZVxuICAgIGZCb3VuZC5wcm90b3R5cGUgPSBuZXcgRm5vb3AoKVxuXG4gICAgcmV0dXJuIGZCb3VuZFxuICB9XG59XG4iLCJyZXF1aXJlKFwiLi4vcG9seWZpbGxzL0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuLi9ldmVudHMvb25cIilcbnZhciBjbG9uZSA9IHJlcXVpcmUoXCIuLi9jbG9uZVwiKVxuXG52YXIgYXR0ckNsaWNrID0gXCJkYXRhLXBqYXgtY2xpY2stc3RhdGVcIlxuXG52YXIgZm9ybUFjdGlvbiA9IGZ1bmN0aW9uKGVsLCBldmVudCl7XG5cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zID0ge1xuICAgIHJlcXVlc3RVcmwgOiBlbC5nZXRBdHRyaWJ1dGUoJ2FjdGlvbicpIHx8IHdpbmRvdy5sb2NhdGlvbi5ocmVmLFxuICAgIHJlcXVlc3RNZXRob2QgOiBlbC5nZXRBdHRyaWJ1dGUoJ21ldGhvZCcpIHx8ICdHRVQnLFxuICB9XG5cbiAgLy9jcmVhdGUgYSB0ZXN0YWJsZSB2aXJ0dWFsIGxpbmsgb2YgdGhlIGZvcm0gYWN0aW9uXG4gIHZhciB2aXJ0TGlua0VsZW1lbnQgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCdhJyk7XG4gIHZpcnRMaW5rRWxlbWVudC5zZXRBdHRyaWJ1dGUoJ2hyZWYnLCB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFVybCk7XG5cbiAgLy8gSWdub3JlIGV4dGVybmFsIGxpbmtzLlxuICBpZiAodmlydExpbmtFbGVtZW50LnByb3RvY29sICE9PSB3aW5kb3cubG9jYXRpb24ucHJvdG9jb2wgfHwgdmlydExpbmtFbGVtZW50Lmhvc3QgIT09IHdpbmRvdy5sb2NhdGlvbi5ob3N0KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJleHRlcm5hbFwiKTtcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBjbGljayBpZiB3ZSBhcmUgb24gYW4gYW5jaG9yIG9uIHRoZSBzYW1lIHBhZ2VcbiAgaWYgKHZpcnRMaW5rRWxlbWVudC5wYXRobmFtZSA9PT0gd2luZG93LmxvY2F0aW9uLnBhdGhuYW1lICYmIHZpcnRMaW5rRWxlbWVudC5oYXNoLmxlbmd0aCA+IDApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1wcmVzZW50XCIpO1xuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGVtcHR5IGFuY2hvciBcImZvby5odG1sI1wiXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdICsgXCIjXCIpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1lbXB0eVwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gaWYgZGVjbGFyZWQgYXMgYSBmdWxsIHJlbG9hZCwganVzdCBub3JtYWxseSBzdWJtaXQgdGhlIGZvcm1cbiAgaWYgKCB0aGlzLm9wdGlvbnMuY3VycmVudFVybEZ1bGxSZWxvYWQpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInJlbG9hZFwiKTtcbiAgICByZXR1cm47XG4gIH1cblxuICBldmVudC5wcmV2ZW50RGVmYXVsdCgpXG4gIHZhciBuYW1lTGlzdCA9IFtdO1xuICB2YXIgcGFyYW1PYmplY3QgPSBbXTtcbiAgZm9yKHZhciBlbGVtZW50S2V5IGluIGVsLmVsZW1lbnRzKSB7XG4gICAgdmFyIGVsZW1lbnQgPSBlbC5lbGVtZW50c1tlbGVtZW50S2V5XTtcbiAgICBpZiAoISFlbGVtZW50Lm5hbWUgJiYgZWxlbWVudC5hdHRyaWJ1dGVzICE9PSB1bmRlZmluZWQgJiYgZWxlbWVudC50YWdOYW1lLnRvTG93ZXJDYXNlKCkgIT09ICdidXR0b24nKXtcbiAgICAgIGlmIChcbiAgICAgICAgKGVsZW1lbnQudHlwZSAhPT0gJ2NoZWNrYm94JyAmJiBlbGVtZW50LnR5cGUgIT09ICdyYWRpbycpIHx8IGVsZW1lbnQuY2hlY2tlZFxuICAgICAgKSB7XG4gICAgICAgIGlmKG5hbWVMaXN0LmluZGV4T2YoZWxlbWVudC5uYW1lKSA9PT0gLTEpe1xuICAgICAgICAgIG5hbWVMaXN0LnB1c2goZWxlbWVudC5uYW1lKTtcbiAgICAgICAgICBwYXJhbU9iamVjdC5wdXNoKHsgbmFtZTogZW5jb2RlVVJJQ29tcG9uZW50KGVsZW1lbnQubmFtZSksIHZhbHVlOiBlbmNvZGVVUklDb21wb25lbnQoZWxlbWVudC52YWx1ZSl9KTtcbiAgICAgICAgfVxuICAgICAgfVxuICAgIH1cbiAgfVxuXG5cblxuICAvL0NyZWF0aW5nIGEgZ2V0U3RyaW5nXG4gIHZhciBwYXJhbXNTdHJpbmcgPSAocGFyYW1PYmplY3QubWFwKGZ1bmN0aW9uKHZhbHVlKXtyZXR1cm4gdmFsdWUubmFtZStcIj1cIit2YWx1ZS52YWx1ZTt9KSkuam9pbignJicpO1xuXG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0UGF5bG9hZCA9IHBhcmFtT2JqZWN0O1xuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMucmVxdWVzdFBheWxvYWRTdHJpbmcgPSBwYXJhbXNTdHJpbmc7XG5cbiAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJzdWJtaXRcIik7XG5cbiAgdGhpcy5sb2FkVXJsKHZpcnRMaW5rRWxlbWVudC5ocmVmLCBjbG9uZSh0aGlzLm9wdGlvbnMpKVxuXG59O1xuXG52YXIgaXNEZWZhdWx0UHJldmVudGVkID0gZnVuY3Rpb24oZXZlbnQpIHtcbiAgcmV0dXJuIGV2ZW50LmRlZmF1bHRQcmV2ZW50ZWQgfHwgZXZlbnQucmV0dXJuVmFsdWUgPT09IGZhbHNlO1xufTtcblxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHZhciB0aGF0ID0gdGhpc1xuXG4gIG9uKGVsLCBcInN1Ym1pdFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBmb3JtQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICB9KVxuXG4gIG9uKGVsLCBcImtleXVwXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuXG4gICAgaWYgKGV2ZW50LmtleUNvZGUgPT0gMTMpIHtcbiAgICAgIGZvcm1BY3Rpb24uY2FsbCh0aGF0LCBlbCwgZXZlbnQpXG4gICAgfVxuICB9LmJpbmQodGhpcykpXG59XG4iLCJyZXF1aXJlKFwiLi4vcG9seWZpbGxzL0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kXCIpXG5cbnZhciBvbiA9IHJlcXVpcmUoXCIuLi9ldmVudHMvb25cIilcbnZhciBjbG9uZSA9IHJlcXVpcmUoXCIuLi9jbG9uZVwiKVxuXG52YXIgYXR0ckNsaWNrID0gXCJkYXRhLXBqYXgtY2xpY2stc3RhdGVcIlxudmFyIGF0dHJLZXkgPSBcImRhdGEtcGpheC1rZXl1cC1zdGF0ZVwiXG5cbnZhciBsaW5rQWN0aW9uID0gZnVuY3Rpb24oZWwsIGV2ZW50KSB7XG4gIC8vIERvbuKAmXQgYnJlYWsgYnJvd3NlciBzcGVjaWFsIGJlaGF2aW9yIG9uIGxpbmtzIChsaWtlIHBhZ2UgaW4gbmV3IHdpbmRvdylcbiAgaWYgKGV2ZW50LndoaWNoID4gMSB8fCBldmVudC5tZXRhS2V5IHx8IGV2ZW50LmN0cmxLZXkgfHwgZXZlbnQuc2hpZnRLZXkgfHwgZXZlbnQuYWx0S2V5KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJtb2RpZmllclwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gd2UgZG8gdGVzdCBvbiBocmVmIG5vdyB0byBwcmV2ZW50IHVuZXhwZWN0ZWQgYmVoYXZpb3IgaWYgZm9yIHNvbWUgcmVhc29uXG4gIC8vIHVzZXIgaGF2ZSBocmVmIHRoYXQgY2FuIGJlIGR5bmFtaWNhbGx5IHVwZGF0ZWRcblxuICAvLyBJZ25vcmUgZXh0ZXJuYWwgbGlua3MuXG4gIGlmIChlbC5wcm90b2NvbCAhPT0gd2luZG93LmxvY2F0aW9uLnByb3RvY29sIHx8IGVsLmhvc3QgIT09IHdpbmRvdy5sb2NhdGlvbi5ob3N0KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJleHRlcm5hbFwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGNsaWNrIGlmIHdlIGFyZSBvbiBhbiBhbmNob3Igb24gdGhlIHNhbWUgcGFnZVxuICBpZiAoZWwucGF0aG5hbWUgPT09IHdpbmRvdy5sb2NhdGlvbi5wYXRobmFtZSAmJiBlbC5oYXNoLmxlbmd0aCA+IDApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1wcmVzZW50XCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgYW5jaG9ycyBvbiB0aGUgc2FtZSBwYWdlIChrZWVwIG5hdGl2ZSBiZWhhdmlvcilcbiAgaWYgKGVsLmhhc2ggJiYgZWwuaHJlZi5yZXBsYWNlKGVsLmhhc2gsIFwiXCIpID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5yZXBsYWNlKGxvY2F0aW9uLmhhc2gsIFwiXCIpKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3JcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBlbXB0eSBhbmNob3IgXCJmb28uaHRtbCNcIlxuICBpZiAoZWwuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdICsgXCIjXCIpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1lbXB0eVwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgZXZlbnQucHJldmVudERlZmF1bHQoKVxuXG4gIC8vIGRvbuKAmXQgZG8gXCJub3RoaW5nXCIgaWYgdXNlciB0cnkgdG8gcmVsb2FkIHRoZSBwYWdlIGJ5IGNsaWNraW5nIHRoZSBzYW1lIGxpbmsgdHdpY2VcbiAgaWYgKFxuICAgIHRoaXMub3B0aW9ucy5jdXJyZW50VXJsRnVsbFJlbG9hZCAmJlxuICAgIGVsLmhyZWYgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnNwbGl0KFwiI1wiKVswXVxuICApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInJlbG9hZFwiKVxuICAgIHRoaXMucmVsb2FkKClcbiAgICByZXR1cm5cbiAgfVxuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgPSB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgfHwge307XG4gIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwibG9hZFwiKVxuICB0aGlzLmxvYWRVcmwoZWwuaHJlZiwgY2xvbmUodGhpcy5vcHRpb25zKSlcbn1cblxudmFyIGlzRGVmYXVsdFByZXZlbnRlZCA9IGZ1bmN0aW9uKGV2ZW50KSB7XG4gIHJldHVybiBldmVudC5kZWZhdWx0UHJldmVudGVkIHx8IGV2ZW50LnJldHVyblZhbHVlID09PSBmYWxzZTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB2YXIgdGhhdCA9IHRoaXNcblxuICBvbihlbCwgXCJjbGlja1wiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBsaW5rQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICB9KVxuXG4gIG9uKGVsLCBcImtleXVwXCIsIGZ1bmN0aW9uKGV2ZW50KSB7XG4gICAgaWYgKGlzRGVmYXVsdFByZXZlbnRlZChldmVudCkpIHtcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIC8vIERvbuKAmXQgYnJlYWsgYnJvd3NlciBzcGVjaWFsIGJlaGF2aW9yIG9uIGxpbmtzIChsaWtlIHBhZ2UgaW4gbmV3IHdpbmRvdylcbiAgICBpZiAoZXZlbnQud2hpY2ggPiAxIHx8IGV2ZW50Lm1ldGFLZXkgfHwgZXZlbnQuY3RybEtleSB8fCBldmVudC5zaGlmdEtleSB8fCBldmVudC5hbHRLZXkpIHtcbiAgICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyS2V5LCBcIm1vZGlmaWVyXCIpXG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBpZiAoZXZlbnQua2V5Q29kZSA9PSAxMykge1xuICAgICAgbGlua0FjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgICB9XG4gIH0uYmluZCh0aGlzKSlcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgcmV0dXJuIGVsLnF1ZXJ5U2VsZWN0b3JBbGwodGhpcy5vcHRpb25zLmVsZW1lbnRzKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbigpIHtcbiAgaWYgKCh0aGlzLm9wdGlvbnMuZGVidWcgJiYgdGhpcy5vcHRpb25zLmxvZ09iamVjdCkpIHtcbiAgICBpZiAodHlwZW9mIHRoaXMub3B0aW9ucy5sb2dPYmplY3QubG9nID09PSBcImZ1bmN0aW9uXCIpIHtcbiAgICAgIHRoaXMub3B0aW9ucy5sb2dPYmplY3QubG9nLmFwcGx5KHRoaXMub3B0aW9ucy5sb2dPYmplY3QsIFsnUEpBWCAtPicsYXJndW1lbnRzXSk7XG4gICAgfVxuICAgIC8vIGllIGlzIHdlaXJkXG4gICAgZWxzZSBpZiAodGhpcy5vcHRpb25zLmxvZ09iamVjdC5sb2cpIHtcbiAgICAgIHRoaXMub3B0aW9ucy5sb2dPYmplY3QubG9nKFsnUEpBWCAtPicsYXJndW1lbnRzXSk7XG4gICAgfVxuICB9XG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG52YXIgcGFyc2VFbGVtZW50VW5sb2FkID0gcmVxdWlyZShcIi4vcGFyc2UtZWxlbWVudC11bmxvYWRcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICBmb3JFYWNoRWxzKHRoaXMuZ2V0RWxlbWVudHMoZWwpLCBwYXJzZUVsZW1lbnRVbmxvYWQsIHRoaXMpXG59XG4iLCJ2YXIgZm9yRWFjaEVscyA9IHJlcXVpcmUoXCIuLi9mb3JlYWNoLWVsc1wiKVxuXG52YXIgcGFyc2VFbGVtZW50ID0gcmVxdWlyZShcIi4vcGFyc2UtZWxlbWVudFwiKVxuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIGZvckVhY2hFbHModGhpcy5nZXRFbGVtZW50cyhlbCksIHBhcnNlRWxlbWVudCwgdGhpcylcbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgc3dpdGNoIChlbC50YWdOYW1lLnRvTG93ZXJDYXNlKCkpIHtcbiAgY2FzZSBcImFcIjpcbiAgICAvLyBvbmx5IGF0dGFjaCBsaW5rIGlmIGVsIGRvZXMgbm90IGFscmVhZHkgaGF2ZSBsaW5rIGF0dGFjaGVkXG4gICAgaWYgKCFlbC5oYXNBdHRyaWJ1dGUoJ2RhdGEtcGpheC1jbGljay1zdGF0ZScpKSB7XG4gICAgICB0aGlzLnVuYXR0YWNoTGluayhlbClcbiAgICB9XG4gICAgYnJlYWtcblxuICAgIGNhc2UgXCJmb3JtXCI6XG4gICAgICAvLyBvbmx5IGF0dGFjaCBsaW5rIGlmIGVsIGRvZXMgbm90IGFscmVhZHkgaGF2ZSBsaW5rIGF0dGFjaGVkXG4gICAgICBpZiAoIWVsLmhhc0F0dHJpYnV0ZSgnZGF0YS1wamF4LWNsaWNrLXN0YXRlJykpIHtcbiAgICAgICAgdGhpcy51bmF0dGFjaEZvcm0oZWwpXG4gICAgICB9XG4gICAgYnJlYWtcblxuICBkZWZhdWx0OlxuICAgIHRocm93IFwiUGpheCBjYW4gb25seSBiZSBhcHBsaWVkIG9uIDxhPiBvciA8Zm9ybT4gc3VibWl0XCJcbiAgfVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICBzd2l0Y2ggKGVsLnRhZ05hbWUudG9Mb3dlckNhc2UoKSkge1xuICBjYXNlIFwiYVwiOlxuICAgIC8vIG9ubHkgYXR0YWNoIGxpbmsgaWYgZWwgZG9lcyBub3QgYWxyZWFkeSBoYXZlIGxpbmsgYXR0YWNoZWRcbiAgICBpZiAoIWVsLmhhc0F0dHJpYnV0ZSgnZGF0YS1wamF4LWNsaWNrLXN0YXRlJykpIHtcbiAgICAgIHRoaXMuYXR0YWNoTGluayhlbClcbiAgICB9XG4gICAgYnJlYWtcblxuICAgIGNhc2UgXCJmb3JtXCI6XG4gICAgICAvLyBvbmx5IGF0dGFjaCBsaW5rIGlmIGVsIGRvZXMgbm90IGFscmVhZHkgaGF2ZSBsaW5rIGF0dGFjaGVkXG4gICAgICBpZiAoIWVsLmhhc0F0dHJpYnV0ZSgnZGF0YS1wamF4LWNsaWNrLXN0YXRlJykpIHtcbiAgICAgICAgdGhpcy5hdHRhY2hGb3JtKGVsKVxuICAgICAgfVxuICAgIGJyZWFrXG5cbiAgZGVmYXVsdDpcbiAgICB0aHJvdyBcIlBqYXggY2FuIG9ubHkgYmUgYXBwbGllZCBvbiA8YT4gb3IgPGZvcm0+IHN1Ym1pdFwiXG4gIH1cbn1cbiIsIi8qIGdsb2JhbCBfZ2FxOiB0cnVlLCBnYTogdHJ1ZSAqL1xuXG5tb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKG9wdGlvbnMpe1xuICB0aGlzLm9wdGlvbnMgPSBvcHRpb25zXG4gIHRoaXMub3B0aW9ucy5lbGVtZW50cyA9IHRoaXMub3B0aW9ucy5lbGVtZW50cyB8fCBcImFbaHJlZl0sIGZvcm1bYWN0aW9uXVwiLFxuICB0aGlzLm9wdGlvbnMucmVSZW5kZXJDU1MgPSB0aGlzLm9wdGlvbnMucmVSZW5kZXJDU1MgfHwgZmFsc2UsXG4gIHRoaXMub3B0aW9ucy5mb3JjZVJlZGlyZWN0T25GYWlsID0gdGhpcy5vcHRpb25zLmZvcmNlUmVkaXJlY3RPbkZhaWwgfHwgZmFsc2UsXG4gIHRoaXMub3B0aW9ucy5zY3JpcHRsb2FkdGltZW91dCA9IHRoaXMub3B0aW9ucy5zY3JpcHRsb2FkdGltZW91dCB8fCAxMDAwLFxuICB0aGlzLm9wdGlvbnMubWFpblNjcmlwdEVsZW1lbnQgPSB0aGlzLm9wdGlvbnMubWFpblNjcmlwdEVsZW1lbnQgfHwgXCJoZWFkXCJcbiAgdGhpcy5vcHRpb25zLnJlbW92ZVNjcmlwdHNBZnRlclBhcnNpbmcgPSB0aGlzLm9wdGlvbnMucmVtb3ZlU2NyaXB0c0FmdGVyUGFyc2luZyB8fCB0cnVlXG4gIHRoaXMub3B0aW9ucy5sb2dPYmplY3QgPSB0aGlzLm9wdGlvbnMubG9nT2JqZWN0IHx8IGNvbnNvbGVcbiAgdGhpcy5vcHRpb25zLnNlbGVjdG9ycyA9IHRoaXMub3B0aW9ucy5zZWxlY3RvcnMgfHwgW1widGl0bGVcIiwgXCIuanMtUGpheFwiXVxuICB0aGlzLm9wdGlvbnMuc3dpdGNoZXMgPSB0aGlzLm9wdGlvbnMuc3dpdGNoZXMgfHwge31cbiAgdGhpcy5vcHRpb25zLnN3aXRjaGVzT3B0aW9ucyA9IHRoaXMub3B0aW9ucy5zd2l0Y2hlc09wdGlvbnMgfHwge31cbiAgdGhpcy5vcHRpb25zLmhpc3RvcnkgPSB0aGlzLm9wdGlvbnMuaGlzdG9yeSB8fCB0cnVlXG4gIHRoaXMub3B0aW9ucy5hbmFseXRpY3MgPSB0aGlzLm9wdGlvbnMuYW5hbHl0aWNzIHx8IGZ1bmN0aW9uKCkge1xuICAgIC8vIG9wdGlvbnMuYmFja3dhcmQgb3Igb3B0aW9ucy5mb3dhcmQgY2FuIGJlIHRydWUgb3IgdW5kZWZpbmVkXG4gICAgLy8gYnkgZGVmYXVsdCwgd2UgZG8gdHJhY2sgYmFjay9mb3dhcmQgaGl0XG4gICAgLy8gaHR0cHM6Ly9wcm9kdWN0Zm9ydW1zLmdvb2dsZS5jb20vZm9ydW0vIyF0b3BpYy9hbmFseXRpY3MvV1Z3TURqTGhYWWtcbiAgICBpZiAod2luZG93Ll9nYXEpIHtcbiAgICAgIF9nYXEucHVzaChbXCJfdHJhY2tQYWdldmlld1wiXSlcbiAgICB9XG4gICAgaWYgKHdpbmRvdy5nYSkge1xuICAgICAgZ2EoXCJzZW5kXCIsIFwicGFnZXZpZXdcIiwge3BhZ2U6IGxvY2F0aW9uLnBhdGhuYW1lLCB0aXRsZTogZG9jdW1lbnQudGl0bGV9KVxuICAgIH1cbiAgfVxuICB0aGlzLm9wdGlvbnMuc2Nyb2xsVG8gPSAodHlwZW9mIHRoaXMub3B0aW9ucy5zY3JvbGxUbyA9PT0gJ3VuZGVmaW5lZCcpID8gMCA6IHRoaXMub3B0aW9ucy5zY3JvbGxUbztcbiAgdGhpcy5vcHRpb25zLmNhY2hlQnVzdCA9ICh0eXBlb2YgdGhpcy5vcHRpb25zLmNhY2hlQnVzdCA9PT0gJ3VuZGVmaW5lZCcpID8gdHJ1ZSA6IHRoaXMub3B0aW9ucy5jYWNoZUJ1c3RcbiAgdGhpcy5vcHRpb25zLmRlYnVnID0gdGhpcy5vcHRpb25zLmRlYnVnIHx8IGZhbHNlXG5cbiAgLy8gd2UgY2Fu4oCZdCByZXBsYWNlIGJvZHkub3V0ZXJIVE1MIG9yIGhlYWQub3V0ZXJIVE1MXG4gIC8vIGl0IGNyZWF0ZSBhIGJ1ZyB3aGVyZSBuZXcgYm9keSBvciBuZXcgaGVhZCBhcmUgY3JlYXRlZCBpbiB0aGUgZG9tXG4gIC8vIGlmIHlvdSBzZXQgaGVhZC5vdXRlckhUTUwsIGEgbmV3IGJvZHkgdGFnIGlzIGFwcGVuZGVkLCBzbyB0aGUgZG9tIGdldCAyIGJvZHlcbiAgLy8gJiBpdCBicmVhayB0aGUgc3dpdGNoRmFsbGJhY2sgd2hpY2ggcmVwbGFjZSBoZWFkICYgYm9keVxuICBpZiAoIXRoaXMub3B0aW9ucy5zd2l0Y2hlcy5oZWFkKSB7XG4gICAgdGhpcy5vcHRpb25zLnN3aXRjaGVzLmhlYWQgPSB0aGlzLnN3aXRjaEVsZW1lbnRzQWx0XG4gIH1cbiAgaWYgKCF0aGlzLm9wdGlvbnMuc3dpdGNoZXMuYm9keSkge1xuICAgIHRoaXMub3B0aW9ucy5zd2l0Y2hlcy5ib2R5ID0gdGhpcy5zd2l0Y2hFbGVtZW50c0FsdFxuICB9XG4gIGlmICh0eXBlb2Ygb3B0aW9ucy5hbmFseXRpY3MgIT09IFwiZnVuY3Rpb25cIikge1xuICAgIG9wdGlvbnMuYW5hbHl0aWNzID0gZnVuY3Rpb24oKSB7fVxuICB9XG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGVsKSB7XG4gIHRoaXMucGFyc2VET00oZWwgfHwgZG9jdW1lbnQpXG59XG4iLCJyZXF1aXJlKFwiLi4vcG9seWZpbGxzL0Z1bmN0aW9uLnByb3RvdHlwZS5iaW5kXCIpXG5cbnZhciBvZmYgPSByZXF1aXJlKFwiLi4vZXZlbnRzL29mZlwiKVxudmFyIGNsb25lID0gcmVxdWlyZShcIi4uL2Nsb25lXCIpXG5cbnZhciBhdHRyQ2xpY2sgPSBcImRhdGEtcGpheC1jbGljay1zdGF0ZVwiXG5cbnZhciBmb3JtQWN0aW9uID0gZnVuY3Rpb24oZWwsIGV2ZW50KXtcblxuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgPSB7XG4gICAgcmVxdWVzdFVybCA6IGVsLmdldEF0dHJpYnV0ZSgnYWN0aW9uJykgfHwgd2luZG93LmxvY2F0aW9uLmhyZWYsXG4gICAgcmVxdWVzdE1ldGhvZCA6IGVsLmdldEF0dHJpYnV0ZSgnbWV0aG9kJykgfHwgJ0dFVCcsXG4gIH1cblxuICAvL2NyZWF0ZSBhIHRlc3RhYmxlIHZpcnR1YWwgbGluayBvZiB0aGUgZm9ybSBhY3Rpb25cbiAgdmFyIHZpcnRMaW5rRWxlbWVudCA9IGRvY3VtZW50LmNyZWF0ZUVsZW1lbnQoJ2EnKTtcbiAgdmlydExpbmtFbGVtZW50LnNldEF0dHJpYnV0ZSgnaHJlZicsIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0VXJsKTtcblxuICAvLyBJZ25vcmUgZXh0ZXJuYWwgbGlua3MuXG4gIGlmICh2aXJ0TGlua0VsZW1lbnQucHJvdG9jb2wgIT09IHdpbmRvdy5sb2NhdGlvbi5wcm90b2NvbCB8fCB2aXJ0TGlua0VsZW1lbnQuaG9zdCAhPT0gd2luZG93LmxvY2F0aW9uLmhvc3QpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImV4dGVybmFsXCIpO1xuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGNsaWNrIGlmIHdlIGFyZSBvbiBhbiBhbmNob3Igb24gdGhlIHNhbWUgcGFnZVxuICBpZiAodmlydExpbmtFbGVtZW50LnBhdGhuYW1lID09PSB3aW5kb3cubG9jYXRpb24ucGF0aG5hbWUgJiYgdmlydExpbmtFbGVtZW50Lmhhc2gubGVuZ3RoID4gMCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLXByZXNlbnRcIik7XG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgZW1wdHkgYW5jaG9yIFwiZm9vLmh0bWwjXCJcbiAgaWYgKHZpcnRMaW5rRWxlbWVudC5ocmVmID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5zcGxpdChcIiNcIilbMF0gKyBcIiNcIikge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwiYW5jaG9yLWVtcHR5XCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBpZiBkZWNsYXJlZCBhcyBhIGZ1bGwgcmVsb2FkLCBqdXN0IG5vcm1hbGx5IHN1Ym1pdCB0aGUgZm9ybVxuICBpZiAoIHRoaXMub3B0aW9ucy5jdXJyZW50VXJsRnVsbFJlbG9hZCkge1xuICAgIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwicmVsb2FkXCIpO1xuICAgIHJldHVybjtcbiAgfVxuXG4gIGV2ZW50LnByZXZlbnREZWZhdWx0KClcbiAgdmFyIG5hbWVMaXN0ID0gW107XG4gIHZhciBwYXJhbU9iamVjdCA9IFtdO1xuICBmb3IodmFyIGVsZW1lbnRLZXkgaW4gZWwuZWxlbWVudHMpIHtcbiAgICB2YXIgZWxlbWVudCA9IGVsLmVsZW1lbnRzW2VsZW1lbnRLZXldO1xuICAgIGlmICghIWVsZW1lbnQubmFtZSAmJiBlbGVtZW50LmF0dHJpYnV0ZXMgIT09IHVuZGVmaW5lZCAmJiBlbGVtZW50LnRhZ05hbWUudG9Mb3dlckNhc2UoKSAhPT0gJ2J1dHRvbicpe1xuICAgICAgaWYgKFxuICAgICAgICAoZWxlbWVudC50eXBlICE9PSAnY2hlY2tib3gnICYmIGVsZW1lbnQudHlwZSAhPT0gJ3JhZGlvJykgfHwgZWxlbWVudC5jaGVja2VkXG4gICAgICApIHtcbiAgICAgICAgaWYobmFtZUxpc3QuaW5kZXhPZihlbGVtZW50Lm5hbWUpID09PSAtMSl7XG4gICAgICAgICAgbmFtZUxpc3QucHVzaChlbGVtZW50Lm5hbWUpO1xuICAgICAgICAgIHBhcmFtT2JqZWN0LnB1c2goeyBuYW1lOiBlbmNvZGVVUklDb21wb25lbnQoZWxlbWVudC5uYW1lKSwgdmFsdWU6IGVuY29kZVVSSUNvbXBvbmVudChlbGVtZW50LnZhbHVlKX0pO1xuICAgICAgICB9XG4gICAgICB9XG4gICAgfVxuICB9XG5cblxuXG4gIC8vQ3JlYXRpbmcgYSBnZXRTdHJpbmdcbiAgdmFyIHBhcmFtc1N0cmluZyA9IChwYXJhbU9iamVjdC5tYXAoZnVuY3Rpb24odmFsdWUpe3JldHVybiB2YWx1ZS5uYW1lK1wiPVwiK3ZhbHVlLnZhbHVlO30pKS5qb2luKCcmJyk7XG5cbiAgdGhpcy5vcHRpb25zLnJlcXVlc3RPcHRpb25zLnJlcXVlc3RQYXlsb2FkID0gcGFyYW1PYmplY3Q7XG4gIHRoaXMub3B0aW9ucy5yZXF1ZXN0T3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyA9IHBhcmFtc1N0cmluZztcblxuICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInN1Ym1pdFwiKTtcblxuICB0aGlzLmxvYWRVcmwodmlydExpbmtFbGVtZW50LmhyZWYsIGNsb25lKHRoaXMub3B0aW9ucykpXG5cbn07XG5cbnZhciBpc0RlZmF1bHRQcmV2ZW50ZWQgPSBmdW5jdGlvbihldmVudCkge1xuICByZXR1cm4gZXZlbnQuZGVmYXVsdFByZXZlbnRlZCB8fCBldmVudC5yZXR1cm5WYWx1ZSA9PT0gZmFsc2U7XG59O1xuXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oZWwpIHtcbiAgdmFyIHRoYXQgPSB0aGlzXG5cbiAgb2ZmKGVsLCBcInN1Ym1pdFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cbiAgICBmb3JtQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICB9KVxuXG4gIG9mZihlbCwgXCJrZXl1cFwiLCBmdW5jdGlvbihldmVudCkge1xuICAgIGlmIChpc0RlZmF1bHRQcmV2ZW50ZWQoZXZlbnQpKSB7XG4gICAgICByZXR1cm5cbiAgICB9XG5cblxuICAgIGlmIChldmVudC5rZXlDb2RlID09IDEzKSB7XG4gICAgICBmb3JtQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICAgIH1cbiAgfS5iaW5kKHRoaXMpKVxufVxuIiwicmVxdWlyZShcIi4uL3BvbHlmaWxscy9GdW5jdGlvbi5wcm90b3R5cGUuYmluZFwiKVxuXG52YXIgb2ZmID0gcmVxdWlyZShcIi4uL2V2ZW50cy9vZmZcIilcbnZhciBjbG9uZSA9IHJlcXVpcmUoXCIuLi9jbG9uZVwiKVxuXG52YXIgYXR0ckNsaWNrID0gXCJkYXRhLXBqYXgtY2xpY2stc3RhdGVcIlxudmFyIGF0dHJLZXkgPSBcImRhdGEtcGpheC1rZXl1cC1zdGF0ZVwiXG5cbnZhciBsaW5rQWN0aW9uID0gZnVuY3Rpb24oZWwsIGV2ZW50KSB7XG4gIC8vIERvbuKAmXQgYnJlYWsgYnJvd3NlciBzcGVjaWFsIGJlaGF2aW9yIG9uIGxpbmtzIChsaWtlIHBhZ2UgaW4gbmV3IHdpbmRvdylcbiAgaWYgKGV2ZW50LndoaWNoID4gMSB8fCBldmVudC5tZXRhS2V5IHx8IGV2ZW50LmN0cmxLZXkgfHwgZXZlbnQuc2hpZnRLZXkgfHwgZXZlbnQuYWx0S2V5KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJtb2RpZmllclwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gd2UgZG8gdGVzdCBvbiBocmVmIG5vdyB0byBwcmV2ZW50IHVuZXhwZWN0ZWQgYmVoYXZpb3IgaWYgZm9yIHNvbWUgcmVhc29uXG4gIC8vIHVzZXIgaGF2ZSBocmVmIHRoYXQgY2FuIGJlIGR5bmFtaWNhbGx5IHVwZGF0ZWRcblxuICAvLyBJZ25vcmUgZXh0ZXJuYWwgbGlua3MuXG4gIGlmIChlbC5wcm90b2NvbCAhPT0gd2luZG93LmxvY2F0aW9uLnByb3RvY29sIHx8IGVsLmhvc3QgIT09IHdpbmRvdy5sb2NhdGlvbi5ob3N0KSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJleHRlcm5hbFwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgLy8gSWdub3JlIGNsaWNrIGlmIHdlIGFyZSBvbiBhbiBhbmNob3Igb24gdGhlIHNhbWUgcGFnZVxuICBpZiAoZWwucGF0aG5hbWUgPT09IHdpbmRvdy5sb2NhdGlvbi5wYXRobmFtZSAmJiBlbC5oYXNoLmxlbmd0aCA+IDApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1wcmVzZW50XCIpXG4gICAgcmV0dXJuXG4gIH1cblxuICAvLyBJZ25vcmUgYW5jaG9ycyBvbiB0aGUgc2FtZSBwYWdlIChrZWVwIG5hdGl2ZSBiZWhhdmlvcilcbiAgaWYgKGVsLmhhc2ggJiYgZWwuaHJlZi5yZXBsYWNlKGVsLmhhc2gsIFwiXCIpID09PSB3aW5kb3cubG9jYXRpb24uaHJlZi5yZXBsYWNlKGxvY2F0aW9uLmhhc2gsIFwiXCIpKSB7XG4gICAgZWwuc2V0QXR0cmlidXRlKGF0dHJDbGljaywgXCJhbmNob3JcIilcbiAgICByZXR1cm5cbiAgfVxuXG4gIC8vIElnbm9yZSBlbXB0eSBhbmNob3IgXCJmb28uaHRtbCNcIlxuICBpZiAoZWwuaHJlZiA9PT0gd2luZG93LmxvY2F0aW9uLmhyZWYuc3BsaXQoXCIjXCIpWzBdICsgXCIjXCIpIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcImFuY2hvci1lbXB0eVwiKVxuICAgIHJldHVyblxuICB9XG5cbiAgZXZlbnQucHJldmVudERlZmF1bHQoKVxuXG4gIC8vIGRvbuKAmXQgZG8gXCJub3RoaW5nXCIgaWYgdXNlciB0cnkgdG8gcmVsb2FkIHRoZSBwYWdlIGJ5IGNsaWNraW5nIHRoZSBzYW1lIGxpbmsgdHdpY2VcbiAgaWYgKFxuICAgIHRoaXMub3B0aW9ucy5jdXJyZW50VXJsRnVsbFJlbG9hZCAmJlxuICAgIGVsLmhyZWYgPT09IHdpbmRvdy5sb2NhdGlvbi5ocmVmLnNwbGl0KFwiI1wiKVswXVxuICApIHtcbiAgICBlbC5zZXRBdHRyaWJ1dGUoYXR0ckNsaWNrLCBcInJlbG9hZFwiKVxuICAgIHRoaXMucmVsb2FkKClcbiAgICByZXR1cm5cbiAgfVxuICB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgPSB0aGlzLm9wdGlvbnMucmVxdWVzdE9wdGlvbnMgfHwge307XG4gIGVsLnNldEF0dHJpYnV0ZShhdHRyQ2xpY2ssIFwibG9hZFwiKVxuICB0aGlzLmxvYWRVcmwoZWwuaHJlZiwgY2xvbmUodGhpcy5vcHRpb25zKSlcbn1cblxudmFyIGlzRGVmYXVsdFByZXZlbnRlZCA9IGZ1bmN0aW9uKGV2ZW50KSB7XG4gIHJldHVybiBldmVudC5kZWZhdWx0UHJldmVudGVkIHx8IGV2ZW50LnJldHVyblZhbHVlID09PSBmYWxzZTtcbn1cblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbCkge1xuICB2YXIgdGhhdCA9IHRoaXNcblxuICBvZmYoZWwsIFwiY2xpY2tcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgbGlua0FjdGlvbi5jYWxsKHRoYXQsIGVsLCBldmVudClcbiAgfSlcblxuICBvZmYoZWwsIFwia2V5dXBcIiwgZnVuY3Rpb24oZXZlbnQpIHtcbiAgICBpZiAoaXNEZWZhdWx0UHJldmVudGVkKGV2ZW50KSkge1xuICAgICAgcmV0dXJuXG4gICAgfVxuXG4gICAgLy8gRG9u4oCZdCBicmVhayBicm93c2VyIHNwZWNpYWwgYmVoYXZpb3Igb24gbGlua3MgKGxpa2UgcGFnZSBpbiBuZXcgd2luZG93KVxuICAgIGlmIChldmVudC53aGljaCA+IDEgfHwgZXZlbnQubWV0YUtleSB8fCBldmVudC5jdHJsS2V5IHx8IGV2ZW50LnNoaWZ0S2V5IHx8IGV2ZW50LmFsdEtleSkge1xuICAgICAgZWwuc2V0QXR0cmlidXRlKGF0dHJLZXksIFwibW9kaWZpZXJcIilcbiAgICAgIHJldHVyblxuICAgIH1cblxuICAgIGlmIChldmVudC5rZXlDb2RlID09IDEzKSB7XG4gICAgICBsaW5rQWN0aW9uLmNhbGwodGhhdCwgZWwsIGV2ZW50KVxuICAgIH1cbiAgfS5iaW5kKHRoaXMpKVxufVxuIiwibW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbigpIHtcbiAgd2luZG93LmxvY2F0aW9uLnJlbG9hZCgpXG59XG4iLCJtb2R1bGUuZXhwb3J0cyA9IGZ1bmN0aW9uKGxvY2F0aW9uLCBvcHRpb25zLCBjYWxsYmFjaykge1xuICBvcHRpb25zID0gb3B0aW9ucyB8fCB7fTtcbiAgdmFyIHJlcXVlc3RNZXRob2QgPSBvcHRpb25zLnJlcXVlc3RNZXRob2QgfHwgXCJHRVRcIjtcbiAgdmFyIHJlcXVlc3RQYXlsb2FkID0gb3B0aW9ucy5yZXF1ZXN0UGF5bG9hZFN0cmluZyB8fCBudWxsO1xuICB2YXIgcmVxdWVzdCA9IG5ldyBYTUxIdHRwUmVxdWVzdCgpXG5cbiAgcmVxdWVzdC5vbnJlYWR5c3RhdGVjaGFuZ2UgPSBmdW5jdGlvbigpIHtcbiAgICBpZiAocmVxdWVzdC5yZWFkeVN0YXRlID09PSA0KSB7XG4gICAgICBpZiAocmVxdWVzdC5zdGF0dXMgPT09IDIwMCkge1xuICAgICAgICBjYWxsYmFjayhyZXF1ZXN0LnJlc3BvbnNlVGV4dCwgcmVxdWVzdClcbiAgICAgIH1cbiAgICAgIGVsc2Uge1xuICAgICAgICBjYWxsYmFjayhudWxsLCByZXF1ZXN0KVxuICAgICAgfVxuICAgIH1cbiAgfVxuXG4gIC8vIEFkZCBhIHRpbWVzdGFtcCBhcyBwYXJ0IG9mIHRoZSBxdWVyeSBzdHJpbmcgaWYgY2FjaGUgYnVzdGluZyBpcyBlbmFibGVkXG4gIGlmICh0aGlzLm9wdGlvbnMuY2FjaGVCdXN0KSB7XG4gICAgbG9jYXRpb24gKz0gKCEvWz8mXS8udGVzdChsb2NhdGlvbikgPyBcIj9cIiA6IFwiJlwiKSArIG5ldyBEYXRlKCkuZ2V0VGltZSgpXG4gIH1cblxuICByZXF1ZXN0Lm9wZW4ocmVxdWVzdE1ldGhvZC50b1VwcGVyQ2FzZSgpLCBsb2NhdGlvbiwgdHJ1ZSlcbiAgcmVxdWVzdC5zZXRSZXF1ZXN0SGVhZGVyKFwiWC1SZXF1ZXN0ZWQtV2l0aFwiLCBcIlhNTEh0dHBSZXF1ZXN0XCIpXG5cbiAgLy8gQWRkIHRoZSByZXF1ZXN0IHBheWxvYWQgaWYgYXZhaWxhYmxlXG4gIGlmIChvcHRpb25zLnJlcXVlc3RQYXlsb2FkU3RyaW5nICE9IHVuZGVmaW5lZCAmJiBvcHRpb25zLnJlcXVlc3RQYXlsb2FkU3RyaW5nICE9IFwiXCIpIHtcbiAgICAvLyBTZW5kIHRoZSBwcm9wZXIgaGVhZGVyIGluZm9ybWF0aW9uIGFsb25nIHdpdGggdGhlIHJlcXVlc3RcbiAgICByZXF1ZXN0LnNldFJlcXVlc3RIZWFkZXIoXCJDb250ZW50LXR5cGVcIiwgXCJhcHBsaWNhdGlvbi94LXd3dy1mb3JtLXVybGVuY29kZWRcIik7XG4gIH1cblxuICByZXF1ZXN0LnNlbmQocmVxdWVzdFBheWxvYWQpXG5cbiAgcmV0dXJuIHJlcXVlc3Rcbn1cbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcblxudmFyIGRlZmF1bHRTd2l0Y2hlcyA9IHJlcXVpcmUoXCIuL3N3aXRjaGVzXCIpXG5cbm1vZHVsZS5leHBvcnRzID0gZnVuY3Rpb24oc3dpdGNoZXMsIHN3aXRjaGVzT3B0aW9ucywgc2VsZWN0b3JzLCBmcm9tRWwsIHRvRWwsIG9wdGlvbnMpIHtcbiAgc2VsZWN0b3JzLmZvckVhY2goZnVuY3Rpb24oc2VsZWN0b3IpIHtcbiAgICB2YXIgbmV3RWxzID0gZnJvbUVsLnF1ZXJ5U2VsZWN0b3JBbGwoc2VsZWN0b3IpXG4gICAgdmFyIG9sZEVscyA9IHRvRWwucXVlcnlTZWxlY3RvckFsbChzZWxlY3RvcilcbiAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgIHRoaXMubG9nKFwiUGpheCBzd2l0Y2hcIiwgc2VsZWN0b3IsIG5ld0Vscywgb2xkRWxzKVxuICAgIH1cbiAgICBpZiAobmV3RWxzLmxlbmd0aCAhPT0gb2xkRWxzLmxlbmd0aCkge1xuICAgICAgLy8gZm9yRWFjaEVscyhuZXdFbHMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICAvLyAgIHRoaXMubG9nKFwibmV3RWxcIiwgZWwsIGVsLm91dGVySFRNTClcbiAgICAgIC8vIH0sIHRoaXMpXG4gICAgICAvLyBmb3JFYWNoRWxzKG9sZEVscywgZnVuY3Rpb24oZWwpIHtcbiAgICAgIC8vICAgdGhpcy5sb2coXCJvbGRFbFwiLCBlbCwgZWwub3V0ZXJIVE1MKVxuICAgICAgLy8gfSwgdGhpcylcbiAgICAgIHRocm93IFwiRE9NIGRvZXNu4oCZdCBsb29rIHRoZSBzYW1lIG9uIG5ldyBsb2FkZWQgcGFnZTog4oCZXCIgKyBzZWxlY3RvciArIFwi4oCZIC0gbmV3IFwiICsgbmV3RWxzLmxlbmd0aCArIFwiLCBvbGQgXCIgKyBvbGRFbHMubGVuZ3RoXG4gICAgfVxuXG4gICAgZm9yRWFjaEVscyhuZXdFbHMsIGZ1bmN0aW9uKG5ld0VsLCBpKSB7XG4gICAgICB2YXIgb2xkRWwgPSBvbGRFbHNbaV1cbiAgICAgIGlmICh0aGlzLmxvZykge1xuICAgICAgICB0aGlzLmxvZyhcIm5ld0VsXCIsIG5ld0VsLCBcIm9sZEVsXCIsIG9sZEVsKVxuICAgICAgfVxuICAgICAgaWYgKHN3aXRjaGVzW3NlbGVjdG9yXSkge1xuICAgICAgICBzd2l0Y2hlc1tzZWxlY3Rvcl0uYmluZCh0aGlzKShvbGRFbCwgbmV3RWwsIG9wdGlvbnMsIHN3aXRjaGVzT3B0aW9uc1tzZWxlY3Rvcl0pXG4gICAgICB9XG4gICAgICBlbHNlIHtcbiAgICAgICAgZGVmYXVsdFN3aXRjaGVzLm91dGVySFRNTC5iaW5kKHRoaXMpKG9sZEVsLCBuZXdFbCwgb3B0aW9ucylcbiAgICAgIH1cbiAgICB9LCB0aGlzKVxuICB9LCB0aGlzKVxufVxuIiwidmFyIG9uID0gcmVxdWlyZShcIi4vZXZlbnRzL29uLmpzXCIpXG4vLyB2YXIgb2ZmID0gcmVxdWlyZShcIi4vbGliL2V2ZW50cy9vbi5qc1wiKVxuLy8gdmFyIHRyaWdnZXIgPSByZXF1aXJlKFwiLi9saWIvZXZlbnRzL3RyaWdnZXIuanNcIilcblxuXG5tb2R1bGUuZXhwb3J0cyA9IHtcbiAgb3V0ZXJIVE1MOiBmdW5jdGlvbihvbGRFbCwgbmV3RWwpIHtcbiAgICBvbGRFbC5vdXRlckhUTUwgPSBuZXdFbC5vdXRlckhUTUxcbiAgICB0aGlzLm9uU3dpdGNoKClcbiAgfSxcblxuICBpbm5lckhUTUw6IGZ1bmN0aW9uKG9sZEVsLCBuZXdFbCkge1xuICAgIG9sZEVsLmlubmVySFRNTCA9IG5ld0VsLmlubmVySFRNTFxuICAgIG9sZEVsLmNsYXNzTmFtZSA9IG5ld0VsLmNsYXNzTmFtZVxuICAgIHRoaXMub25Td2l0Y2goKVxuICB9LFxuXG4gIHNpZGVCeVNpZGU6IGZ1bmN0aW9uKG9sZEVsLCBuZXdFbCwgb3B0aW9ucywgc3dpdGNoT3B0aW9ucykge1xuICAgIHZhciBmb3JFYWNoID0gQXJyYXkucHJvdG90eXBlLmZvckVhY2hcbiAgICB2YXIgZWxzVG9SZW1vdmUgPSBbXVxuICAgIHZhciBlbHNUb0FkZCA9IFtdXG4gICAgdmFyIGZyYWdUb0FwcGVuZCA9IGRvY3VtZW50LmNyZWF0ZURvY3VtZW50RnJhZ21lbnQoKVxuICAgIC8vIGhlaWdodCB0cmFuc2l0aW9uIGFyZSBzaGl0dHkgb24gc2FmYXJpXG4gICAgLy8gc28gY29tbWVudGVkIGZvciBub3cgKHVudGlsIEkgZm91bmQgc29tZXRoaW5nID8pXG4gICAgLy8gdmFyIHJlbGV2YW50SGVpZ2h0ID0gMFxuICAgIHZhciBhbmltYXRpb25FdmVudE5hbWVzID0gXCJhbmltYXRpb25lbmQgd2Via2l0QW5pbWF0aW9uRW5kIE1TQW5pbWF0aW9uRW5kIG9hbmltYXRpb25lbmRcIlxuICAgIHZhciBhbmltYXRlZEVsc051bWJlciA9IDBcbiAgICB2YXIgc2V4eUFuaW1hdGlvbkVuZCA9IGZ1bmN0aW9uKGUpIHtcbiAgICAgICAgICBpZiAoZS50YXJnZXQgIT0gZS5jdXJyZW50VGFyZ2V0KSB7XG4gICAgICAgICAgICAvLyBlbmQgdHJpZ2dlcmVkIGJ5IGFuIGFuaW1hdGlvbiBvbiBhIGNoaWxkXG4gICAgICAgICAgICByZXR1cm5cbiAgICAgICAgICB9XG5cbiAgICAgICAgICBhbmltYXRlZEVsc051bWJlci0tXG4gICAgICAgICAgaWYgKGFuaW1hdGVkRWxzTnVtYmVyIDw9IDAgJiYgZWxzVG9SZW1vdmUpIHtcbiAgICAgICAgICAgIGVsc1RvUmVtb3ZlLmZvckVhY2goZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgICAgICAgLy8gYnJvd3NpbmcgcXVpY2tseSBjYW4gbWFrZSB0aGUgZWxcbiAgICAgICAgICAgICAgLy8gYWxyZWFkeSByZW1vdmVkIGJ5IGxhc3QgcGFnZSB1cGRhdGUgP1xuICAgICAgICAgICAgICBpZiAoZWwucGFyZW50Tm9kZSkge1xuICAgICAgICAgICAgICAgIGVsLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQoZWwpXG4gICAgICAgICAgICAgIH1cbiAgICAgICAgICAgIH0pXG5cbiAgICAgICAgICAgIGVsc1RvQWRkLmZvckVhY2goZnVuY3Rpb24oZWwpIHtcbiAgICAgICAgICAgICAgZWwuY2xhc3NOYW1lID0gZWwuY2xhc3NOYW1lLnJlcGxhY2UoZWwuZ2V0QXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIiksIFwiXCIpXG4gICAgICAgICAgICAgIGVsLnJlbW92ZUF0dHJpYnV0ZShcImRhdGEtcGpheC1jbGFzc2VzXCIpXG4gICAgICAgICAgICAgIC8vIFBqYXgub2ZmKGVsLCBhbmltYXRpb25FdmVudE5hbWVzLCBzZXh5QW5pbWF0aW9uRW5kLCB0cnVlKVxuICAgICAgICAgICAgfSlcblxuICAgICAgICAgICAgZWxzVG9BZGQgPSBudWxsIC8vIGZyZWUgbWVtb3J5XG4gICAgICAgICAgICBlbHNUb1JlbW92ZSA9IG51bGwgLy8gZnJlZSBtZW1vcnlcblxuICAgICAgICAgICAgLy8gYXNzdW1lIHRoZSBoZWlnaHQgaXMgbm93IHVzZWxlc3MgKGF2b2lkIGJ1ZyBzaW5jZSB0aGVyZSBpcyBvdmVyZmxvdyBoaWRkZW4gb24gdGhlIHBhcmVudClcbiAgICAgICAgICAgIC8vIG9sZEVsLnN0eWxlLmhlaWdodCA9IFwiYXV0b1wiXG5cbiAgICAgICAgICAgIC8vIHRoaXMgaXMgdG8gdHJpZ2dlciBzb21lIHJlcGFpbnQgKGV4YW1wbGU6IHBpY3R1cmVmaWxsKVxuICAgICAgICAgICAgdGhpcy5vblN3aXRjaCgpXG4gICAgICAgICAgICAvLyBQamF4LnRyaWdnZXIod2luZG93LCBcInNjcm9sbFwiKVxuICAgICAgICAgIH1cbiAgICAgICAgfS5iaW5kKHRoaXMpXG5cbiAgICAvLyBGb3JjZSBoZWlnaHQgdG8gYmUgYWJsZSB0byB0cmlnZ2VyIGNzcyBhbmltYXRpb25cbiAgICAvLyBoZXJlIHdlIGdldCB0aGUgcmVsZXZhbnQgaGVpZ2h0XG4gICAgLy8gb2xkRWwucGFyZW50Tm9kZS5hcHBlbmRDaGlsZChuZXdFbClcbiAgICAvLyByZWxldmFudEhlaWdodCA9IG5ld0VsLmdldEJvdW5kaW5nQ2xpZW50UmVjdCgpLmhlaWdodFxuICAgIC8vIG9sZEVsLnBhcmVudE5vZGUucmVtb3ZlQ2hpbGQobmV3RWwpXG4gICAgLy8gb2xkRWwuc3R5bGUuaGVpZ2h0ID0gb2xkRWwuZ2V0Qm91bmRpbmdDbGllbnRSZWN0KCkuaGVpZ2h0ICsgXCJweFwiXG5cbiAgICBzd2l0Y2hPcHRpb25zID0gc3dpdGNoT3B0aW9ucyB8fCB7fVxuXG4gICAgZm9yRWFjaC5jYWxsKG9sZEVsLmNoaWxkTm9kZXMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICBlbHNUb1JlbW92ZS5wdXNoKGVsKVxuICAgICAgaWYgKGVsLmNsYXNzTGlzdCAmJiAhZWwuY2xhc3NMaXN0LmNvbnRhaW5zKFwianMtUGpheC1yZW1vdmVcIikpIHtcbiAgICAgICAgLy8gZm9yIGZhc3Qgc3dpdGNoLCBjbGVhbiBlbGVtZW50IHRoYXQganVzdCBoYXZlIGJlZW4gYWRkZWQsICYgbm90IGNsZWFuZWQgeWV0LlxuICAgICAgICBpZiAoZWwuaGFzQXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIikpIHtcbiAgICAgICAgICBlbC5jbGFzc05hbWUgPSBlbC5jbGFzc05hbWUucmVwbGFjZShlbC5nZXRBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKSwgXCJcIilcbiAgICAgICAgICBlbC5yZW1vdmVBdHRyaWJ1dGUoXCJkYXRhLXBqYXgtY2xhc3Nlc1wiKVxuICAgICAgICB9XG4gICAgICAgIGVsLmNsYXNzTGlzdC5hZGQoXCJqcy1QamF4LXJlbW92ZVwiKVxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MgJiYgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MucmVtb3ZlRWxlbWVudCkge1xuICAgICAgICAgIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLnJlbW92ZUVsZW1lbnQoZWwpXG4gICAgICAgIH1cbiAgICAgICAgaWYgKHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcykge1xuICAgICAgICAgIGVsLmNsYXNzTmFtZSArPSBcIiBcIiArIHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5yZW1vdmUgKyBcIiBcIiArIChvcHRpb25zLmJhY2t3YXJkID8gc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmJhY2t3YXJkIDogc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmZvcndhcmQpXG4gICAgICAgIH1cbiAgICAgICAgYW5pbWF0ZWRFbHNOdW1iZXIrK1xuICAgICAgICBvbihlbCwgYW5pbWF0aW9uRXZlbnROYW1lcywgc2V4eUFuaW1hdGlvbkVuZCwgdHJ1ZSlcbiAgICAgIH1cbiAgICB9KVxuXG4gICAgZm9yRWFjaC5jYWxsKG5ld0VsLmNoaWxkTm9kZXMsIGZ1bmN0aW9uKGVsKSB7XG4gICAgICBpZiAoZWwuY2xhc3NMaXN0KSB7XG4gICAgICAgIHZhciBhZGRDbGFzc2VzID0gXCJcIlxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzKSB7XG4gICAgICAgICAgYWRkQ2xhc3NlcyA9IFwiIGpzLVBqYXgtYWRkIFwiICsgc3dpdGNoT3B0aW9ucy5jbGFzc05hbWVzLmFkZCArIFwiIFwiICsgKG9wdGlvbnMuYmFja3dhcmQgPyBzd2l0Y2hPcHRpb25zLmNsYXNzTmFtZXMuZm9yd2FyZCA6IHN3aXRjaE9wdGlvbnMuY2xhc3NOYW1lcy5iYWNrd2FyZClcbiAgICAgICAgfVxuICAgICAgICBpZiAoc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MgJiYgc3dpdGNoT3B0aW9ucy5jYWxsYmFja3MuYWRkRWxlbWVudCkge1xuICAgICAgICAgIHN3aXRjaE9wdGlvbnMuY2FsbGJhY2tzLmFkZEVsZW1lbnQoZWwpXG4gICAgICAgIH1cbiAgICAgICAgZWwuY2xhc3NOYW1lICs9IGFkZENsYXNzZXNcbiAgICAgICAgZWwuc2V0QXR0cmlidXRlKFwiZGF0YS1wamF4LWNsYXNzZXNcIiwgYWRkQ2xhc3NlcylcbiAgICAgICAgZWxzVG9BZGQucHVzaChlbClcbiAgICAgICAgZnJhZ1RvQXBwZW5kLmFwcGVuZENoaWxkKGVsKVxuICAgICAgICBhbmltYXRlZEVsc051bWJlcisrXG4gICAgICAgIG9uKGVsLCBhbmltYXRpb25FdmVudE5hbWVzLCBzZXh5QW5pbWF0aW9uRW5kLCB0cnVlKVxuICAgICAgfVxuICAgIH0pXG5cbiAgICAvLyBwYXNzIGFsbCBjbGFzc05hbWUgb2YgdGhlIHBhcmVudFxuICAgIG9sZEVsLmNsYXNzTmFtZSA9IG5ld0VsLmNsYXNzTmFtZVxuICAgIG9sZEVsLmFwcGVuZENoaWxkKGZyYWdUb0FwcGVuZClcblxuICAgIC8vIG9sZEVsLnN0eWxlLmhlaWdodCA9IHJlbGV2YW50SGVpZ2h0ICsgXCJweFwiXG4gIH1cbn1cbiIsIm1vZHVsZS5leHBvcnRzID0gKGZ1bmN0aW9uKCkge1xuICB2YXIgY291bnRlciA9IDBcbiAgcmV0dXJuIGZ1bmN0aW9uKCkge1xuICAgIHZhciBpZCA9IChcInBqYXhcIiArIChuZXcgRGF0ZSgpLmdldFRpbWUoKSkpICsgXCJfXCIgKyBjb3VudGVyXG4gICAgY291bnRlcisrXG4gICAgcmV0dXJuIGlkXG4gIH1cbn0pKClcbiIsInZhciBmb3JFYWNoRWxzID0gcmVxdWlyZShcIi4vZm9yZWFjaC1lbHNcIilcblxubW9kdWxlLmV4cG9ydHMgPSBmdW5jdGlvbihlbGVtZW50cywgb2xkRWxlbWVudHMpIHtcbiAgIHRoaXMubG9nKFwic3R5bGVoZWV0cyBvbGQgZWxlbWVudHNcIiwgb2xkRWxlbWVudHMpO1xuICAgdGhpcy5sb2coXCJzdHlsZWhlZXRzIG5ldyBlbGVtZW50c1wiLCBlbGVtZW50cyk7XG4gIHZhciB0b0FycmF5ID0gZnVuY3Rpb24oZW51bWVyYWJsZSl7XG4gICAgICB2YXIgYXJyID0gW107XG4gICAgICBmb3IodmFyIGkgPSBlbnVtZXJhYmxlLmxlbmd0aDsgaS0tOyBhcnIudW5zaGlmdChlbnVtZXJhYmxlW2ldKSk7XG4gICAgICByZXR1cm4gYXJyO1xuICB9O1xuICBmb3JFYWNoRWxzKGVsZW1lbnRzLCBmdW5jdGlvbihuZXdFbCwgaSkge1xuICAgIHZhciBvbGRFbGVtZW50c0FycmF5ID0gdG9BcnJheShvbGRFbGVtZW50cyk7XG4gICAgdmFyIHJlc2VtYmxpbmdPbGQgPSBvbGRFbGVtZW50c0FycmF5LnJlZHVjZShmdW5jdGlvbihhY2MsIG9sZEVsKXtcbiAgICAgIGFjYyA9ICgob2xkRWwuaHJlZiA9PT0gbmV3RWwuaHJlZikgPyBvbGRFbCA6IGFjYyk7XG4gICAgICByZXR1cm4gYWNjO1xuICAgIH0sIG51bGwpO1xuXG4gICAgaWYocmVzZW1ibGluZ09sZCAhPT0gbnVsbCl7XG4gICAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgICAgdGhpcy5sb2coXCJvbGQgc3R5bGVzaGVldCBmb3VuZCBub3QgcmVzZXR0aW5nXCIpO1xuICAgICAgfVxuICAgIH0gZWxzZSB7XG4gICAgICBpZiAodGhpcy5sb2cpIHtcbiAgICAgICAgdGhpcy5sb2coXCJuZXcgc3R5bGVzaGVldCA9PiBhZGQgdG8gaGVhZFwiKTtcbiAgICAgIH1cbiAgICAgIHZhciBoZWFkID0gZG9jdW1lbnQuZ2V0RWxlbWVudHNCeVRhZ05hbWUoICdoZWFkJyApWzBdLFxuICAgICAgIGxpbmsgPSBkb2N1bWVudC5jcmVhdGVFbGVtZW50KCAnbGluaycgKTtcbiAgICAgICAgbGluay5zZXRBdHRyaWJ1dGUoICdocmVmJywgbmV3RWwuaHJlZiApO1xuICAgICAgICBsaW5rLnNldEF0dHJpYnV0ZSggJ3JlbCcsICdzdHlsZXNoZWV0JyApO1xuICAgICAgICBsaW5rLnNldEF0dHJpYnV0ZSggJ3R5cGUnLCAndGV4dC9jc3MnICk7XG4gICAgICAgIGhlYWQuYXBwZW5kQ2hpbGQobGluayk7XG4gICAgfVxuICB9LCB0aGlzKTtcblxufVxuIl19
