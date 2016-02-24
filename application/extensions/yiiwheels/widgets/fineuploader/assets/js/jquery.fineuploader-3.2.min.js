var qq = function (e) {
    "use strict";
    return{hide: function () {
        e.style.display = "none";
        return this
    }, attach: function (t, n) {
        if (e.addEventListener) {
            e.addEventListener(t, n, false)
        } else if (e.attachEvent) {
            e.attachEvent("on" + t, n)
        }
        return function () {
            qq(e).detach(t, n)
        }
    }, detach: function (t, n) {
        if (e.removeEventListener) {
            e.removeEventListener(t, n, false)
        } else if (e.attachEvent) {
            e.detachEvent("on" + t, n)
        }
        return this
    }, contains: function (t) {
        if (e === t) {
            return true
        }
        if (e.contains) {
            return e.contains(t)
        } else {
            return!!(t.compareDocumentPosition(e) & 8)
        }
    }, insertBefore: function (t) {
        t.parentNode.insertBefore(e, t);
        return this
    }, remove: function () {
        e.parentNode.removeChild(e);
        return this
    }, css: function (t) {
        if (t.opacity !== null) {
            if (typeof e.style.opacity !== "string" && typeof e.filters !== "undefined") {
                t.filter = "alpha(opacity=" + Math.round(100 * t.opacity) + ")"
            }
        }
        qq.extend(e.style, t);
        return this
    }, hasClass: function (t) {
        var n = new RegExp("(^| )" + t + "( |$)");
        return n.test(e.className)
    }, addClass: function (t) {
        if (!qq(e).hasClass(t)) {
            e.className += " " + t
        }
        return this
    }, removeClass: function (t) {
        var n = new RegExp("(^| )" + t + "( |$)");
        e.className = e.className.replace(n, " ").replace(/^\s+|\s+$/g, "");
        return this
    }, getByClass: function (t) {
        var n, r = [];
        if (e.querySelectorAll) {
            return e.querySelectorAll("." + t)
        }
        n = e.getElementsByTagName("*");
        qq.each(n, function (e, n) {
            if (qq(n).hasClass(t)) {
                r.push(n)
            }
        });
        return r
    }, children: function () {
        var t = [], n = e.firstChild;
        while (n) {
            if (n.nodeType === 1) {
                t.push(n)
            }
            n = n.nextSibling
        }
        return t
    }, setText: function (t) {
        e.innerText = t;
        e.textContent = t;
        return this
    }, clearText: function () {
        return qq(e).setText("")
    }}
};
qq.log = function (e, t) {
    "use strict";
    if (window.console) {
        if (!t || t === "info") {
            window.console.log(e)
        } else {
            if (window.console[t]) {
                window.console[t](e)
            } else {
                window.console.log("<" + t + "> " + e)
            }
        }
    }
};
qq.isObject = function (e) {
    "use strict";
    return e !== null && e && typeof e === "object" && e.constructor === Object
};
qq.isFunction = function (e) {
    "use strict";
    return typeof e === "function"
};
qq.isFileOrInput = function (e) {
    "use strict";
    if (window.File && e instanceof File) {
        return true
    } else if (window.HTMLInputElement) {
        if (e instanceof HTMLInputElement) {
            if (e.type && e.type.toLowerCase() === "file") {
                return true
            }
        }
    } else if (e.tagName) {
        if (e.tagName.toLowerCase() === "input") {
            if (e.type && e.type.toLowerCase() === "file") {
                return true
            }
        }
    }
    return false
};
qq.isXhrUploadSupported = function () {
    "use strict";
    var e = document.createElement("input");
    e.type = "file";
    return e.multiple !== undefined && typeof File !== "undefined" && typeof FormData !== "undefined" && typeof (new XMLHttpRequest).upload !== "undefined"
};
qq.isFolderDropSupported = function (e) {
    "use strict";
    return e.items && e.items[0].webkitGetAsEntry
};
qq.isFileChunkingSupported = function () {
    "use strict";
    return!qq.android() && qq.isXhrUploadSupported() && (File.prototype.slice || File.prototype.webkitSlice || File.prototype.mozSlice)
};
qq.extend = function (e, t, n) {
    "use strict";
    qq.each(t, function (t, r) {
        if (n && qq.isObject(r)) {
            if (e[t] === undefined) {
                e[t] = {}
            }
            qq.extend(e[t], r, true)
        } else {
            e[t] = r
        }
    })
};
qq.indexOf = function (e, t, n) {
    "use strict";
    if (e.indexOf) {
        return e.indexOf(t, n)
    }
    n = n || 0;
    var r = e.length;
    if (n < 0) {
        n += r
    }
    for (; n < r; n += 1) {
        if (e.hasOwnProperty(n) && e[n] === t) {
            return n
        }
    }
    return-1
};
qq.getUniqueId = function () {
    "use strict";
    return"xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx".replace(/[xy]/g, function (e) {
        var t = Math.random() * 16 | 0, n = e == "x" ? t : t & 3 | 8;
        return n.toString(16)
    })
};
qq.ie = function () {
    "use strict";
    return navigator.userAgent.indexOf("MSIE") !== -1
};
qq.ie10 = function () {
    "use strict";
    return navigator.userAgent.indexOf("MSIE 10") !== -1
};
qq.safari = function () {
    "use strict";
    return navigator.vendor !== undefined && navigator.vendor.indexOf("Apple") !== -1
};
qq.chrome = function () {
    "use strict";
    return navigator.vendor !== undefined && navigator.vendor.indexOf("Google") !== -1
};
qq.firefox = function () {
    "use strict";
    return navigator.userAgent.indexOf("Mozilla") !== -1 && navigator.vendor !== undefined && navigator.vendor === ""
};
qq.windows = function () {
    "use strict";
    return navigator.platform === "Win32"
};
qq.android = function () {
    "use strict";
    return navigator.userAgent.toLowerCase().indexOf("android") !== -1
};
qq.preventDefault = function (e) {
    "use strict";
    if (e.preventDefault) {
        e.preventDefault()
    } else {
        e.returnValue = false
    }
};
qq.toElement = function () {
    "use strict";
    var e = document.createElement("div");
    return function (t) {
        e.innerHTML = t;
        var n = e.firstChild;
        e.removeChild(n);
        return n
    }
}();
qq.each = function (e, t) {
    "use strict";
    var n, r;
    if (e) {
        for (n in e) {
            if (Object.prototype.hasOwnProperty.call(e, n)) {
                r = t(n, e[n]);
                if (r === false) {
                    break
                }
            }
        }
    }
};
qq.obj2url = function (e, t, n) {
    "use strict";
    var r, i, s = [], o = "&", u = function (e, n) {
        var r = t ? /\[\]$/.test(t) ? t : t + "[" + n + "]" : n;
        if (r !== "undefined" && n !== "undefined") {
            s.push(typeof e === "object" ? qq.obj2url(e, r, true) : Object.prototype.toString.call(e) === "[object Function]" ? encodeURIComponent(r) + "=" + encodeURIComponent(e()) : encodeURIComponent(r) + "=" + encodeURIComponent(e))
        }
    };
    if (!n && t) {
        o = /\?/.test(t) ? /\?$/.test(t) ? "" : "&" : "?";
        s.push(t);
        s.push(qq.obj2url(e))
    } else if (Object.prototype.toString.call(e) === "[object Array]" && typeof e !== "undefined") {
        for (r = -1, i = e.length; r < i; r += 1) {
            u(e[r], r)
        }
    } else if (typeof e !== "undefined" && e !== null && typeof e === "object") {
        for (r in e) {
            if (e.hasOwnProperty(r)) {
                u(e[r], r)
            }
        }
    } else {
        s.push(encodeURIComponent(t) + "=" + encodeURIComponent(e))
    }
    if (t) {
        return s.join(o)
    } else {
        return s.join(o).replace(/^&/, "").replace(/%20/g, "+")
    }
};
qq.obj2FormData = function (e, t, n) {
    "use strict";
    if (!t) {
        t = new FormData
    }
    qq.each(e, function (e, r) {
        e = n ? n + "[" + e + "]" : e;
        if (qq.isObject(r)) {
            qq.obj2FormData(r, t, e)
        } else if (qq.isFunction(r)) {
            t.append(encodeURIComponent(e), encodeURIComponent(r()))
        } else {
            t.append(encodeURIComponent(e), encodeURIComponent(r))
        }
    });
    return t
};
qq.obj2Inputs = function (e, t) {
    "use strict";
    var n;
    if (!t) {
        t = document.createElement("form")
    }
    qq.obj2FormData(e, {append: function (e, r) {
        n = document.createElement("input");
        n.setAttribute("name", e);
        n.setAttribute("value", r);
        t.appendChild(n)
    }});
    return t
};
qq.setCookie = function (e, t, n) {
    var r = new Date, i = "";
    if (n) {
        r.setTime(r.getTime() + n * 24 * 60 * 60 * 1e3);
        i = "; expires=" + r.toGMTString()
    }
    document.cookie = e + "=" + t + i + "; path=/"
};
qq.getCookie = function (e) {
    var t = e + "=", n = document.cookie.split(";"), r;
    for (var i = 0; i < n.length; i++) {
        r = n[i];
        while (r.charAt(0) == " ") {
            r = r.substring(1, r.length)
        }
        if (r.indexOf(t) === 0) {
            return r.substring(t.length, r.length)
        }
    }
};
qq.getCookieNames = function (e) {
    var t = document.cookie.split(";"), n = [];
    qq.each(t, function (t, r) {
        r = r.trim();
        var i = r.indexOf("=");
        if (r.match(e)) {
            n.push(r.substr(0, i))
        }
    });
    return n
};
qq.deleteCookie = function (e) {
    qq.setCookie(e, "", -1)
};
qq.areCookiesEnabled = function () {
    var e = Math.random() * 1e5, t = "qqCookieTest:" + e;
    qq.setCookie(t, 1);
    if (qq.getCookie(t)) {
        qq.deleteCookie(t);
        return true
    }
    return false
};
qq.parseJson = function (json) {
    if (typeof JSON.parse === "function") {
        return JSON.parse(json)
    } else {
        return eval("(" + json + ")")
    }
};
qq.DisposeSupport = function () {
    "use strict";
    var e = [];
    return{dispose: function () {
        var t;
        do {
            t = e.shift();
            if (t) {
                t()
            }
        } while (t)
    }, attach: function () {
        var e = arguments;
        this.addDisposer(qq(e[0]).attach.apply(this, Array.prototype.slice.call(arguments, 1)))
    }, addDisposer: function (t) {
        e.push(t)
    }}
};
qq.UploadButton = function (e) {
    this._options = {element: null, multiple: false, acceptFiles: null, name: "file", onChange: function (e) {
    }, hoverClass: "qq-upload-button-hover", focusClass: "qq-upload-button-focus"};
    qq.extend(this._options, e);
    this._disposeSupport = new qq.DisposeSupport;
    this._element = this._options.element;
    qq(this._element).css({position: "relative", overflow: "hidden", direction: "ltr"});
    this._input = this._createInput()
};
qq.UploadButton.prototype = {getInput: function () {
    return this._input
}, reset: function () {
    if (this._input.parentNode) {
        qq(this._input).remove()
    }
    qq(this._element).removeClass(this._options.focusClass);
    this._input = this._createInput()
}, _createInput: function () {
    var e = document.createElement("input");
    if (this._options.multiple) {
        e.setAttribute("multiple", "multiple")
    }
    if (this._options.acceptFiles)e.setAttribute("accept", this._options.acceptFiles);
    e.setAttribute("type", "file");
    e.setAttribute("name", this._options.name);
    qq(e).css({position: "absolute", right: 0, top: 0, fontFamily: "Arial", fontSize: "118px", margin: 0, padding: 0, cursor: "pointer", opacity: 0});
    this._element.appendChild(e);
    var t = this;
    this._disposeSupport.attach(e, "change", function () {
        t._options.onChange(e)
    });
    this._disposeSupport.attach(e, "mouseover", function () {
        qq(t._element).addClass(t._options.hoverClass)
    });
    this._disposeSupport.attach(e, "mouseout", function () {
        qq(t._element).removeClass(t._options.hoverClass)
    });
    this._disposeSupport.attach(e, "focus", function () {
        qq(t._element).addClass(t._options.focusClass)
    });
    this._disposeSupport.attach(e, "blur", function () {
        qq(t._element).removeClass(t._options.focusClass)
    });
    if (window.attachEvent) {
        e.setAttribute("tabIndex", "-1")
    }
    return e
}};
qq.UploadHandler = function (e) {
    "use strict";
    var t = [], n, r, i, s;
    n = {debug: false, forceMultipart: true, paramsInBody: false, paramsStore: {}, endpointStore: {}, maxConnections: 3, uuidParamName: "qquuid", totalFileSizeParamName: "qqtotalfilesize", chunking: {enabled: false, partSize: 2e6, paramNames: {partIndex: "qqpartindex", partByteOffset: "qqpartbyteoffset", chunkSize: "qqchunksize", totalParts: "qqtotalparts", filename: "qqfilename"}}, resume: {enabled: false, id: null, cookiesExpireIn: 7, paramNames: {resuming: "qqresume"}}, log: function (e, t) {
    }, onProgress: function (e, t, n, r) {
    }, onComplete: function (e, t, n, r) {
    }, onCancel: function (e, t) {
    }, onUpload: function (e, t) {
    }, onUploadChunk: function (e, t, n) {
    }, onAutoRetry: function (e, t, n, r) {
    }, onResume: function (e, t, n) {
    }};
    qq.extend(n, e);
    r = n.log;
    i = function (e) {
        var r = qq.indexOf(t, e), i = n.maxConnections, o;
        t.splice(r, 1);
        if (t.length >= i && r < i) {
            o = t[i - 1];
            s.upload(o)
        }
    };
    if (qq.isXhrUploadSupported()) {
        s = new qq.UploadHandlerXhr(n, i, r)
    } else {
        s = new qq.UploadHandlerForm(n, i, r)
    }
    return{add: function (e) {
        return s.add(e)
    }, upload: function (e) {
        var r = t.push(e);
        if (r <= n.maxConnections) {
            return s.upload(e)
        }
    }, retry: function (e) {
        var n = qq.indexOf(t, e);
        if (n >= 0) {
            return s.upload(e, true)
        } else {
            return this.upload(e)
        }
    }, cancel: function (e) {
        r("Cancelling " + e);
        n.paramsStore.remove(e);
        s.cancel(e);
        i(e)
    }, cancelAll: function () {
        qq.each(t, function (e, t) {
            this.cancel(t)
        });
        t = []
    }, getName: function (e) {
        return s.getName(e)
    }, getSize: function (e) {
        if (s.getSize) {
            return s.getSize(e)
        }
    }, getFile: function (e) {
        if (s.getFile) {
            return s.getFile(e)
        }
    }, getQueue: function () {
        return t
    }, reset: function () {
        r("Resetting upload handler");
        t = [];
        s.reset()
    }, getUuid: function (e) {
        return s.getUuid(e)
    }, isValid: function (e) {
        return s.isValid(e)
    }, getResumableFilesData: function () {
        if (s.getResumableFilesData) {
            return s.getResumableFilesData()
        }
        return[]
    }}
};
qq.UploadHandlerForm = function (o, uploadCompleteCallback, logCallback) {
    "use strict";
    function attachLoadEvent(e, t) {
        detachLoadEvents[e.id] = qq(e).attach("load", function () {
            log("Received response for " + e.id);
            if (!e.parentNode) {
                return
            }
            try {
                if (e.contentDocument && e.contentDocument.body && e.contentDocument.body.innerHTML == "false") {
                    return
                }
            } catch (n) {
                log("Error when attempting to access iframe during handling of upload response (" + n + ")", "error")
            }
            t()
        })
    }

    function getIframeContentJson(iframe) {
        var response;
        try {
            var doc = iframe.contentDocument || iframe.contentWindow.document, innerHTML = doc.body.innerHTML;
            log("converting iframe's innerHTML to JSON");
            log("innerHTML = " + innerHTML);
            if (innerHTML && innerHTML.match(/^<pre/i)) {
                innerHTML = doc.body.firstChild.firstChild.nodeValue
            }
            response = eval("(" + innerHTML + ")")
        } catch (error) {
            log("Error when attempting to parse form upload response (" + error + ")", "error");
            response = {success: false}
        }
        return response
    }

    function createIframe(e) {
        var t = qq.toElement('<iframe src="javascript:false;" name="' + e + '" />');
        t.setAttribute("id", e);
        t.style.display = "none";
        document.body.appendChild(t);
        return t
    }

    function createForm(e, t) {
        var n = options.paramsStore.getParams(e), r = options.demoMode ? "GET" : "POST", i = qq.toElement('<form method="' + r + '" enctype="multipart/form-data"></form>'), s = options.endpointStore.getEndpoint(e), o = s;
        n[options.uuidParamName] = uuids[e];
        if (!options.paramsInBody) {
            o = qq.obj2url(n, s)
        } else {
            qq.obj2Inputs(n, i)
        }
        i.setAttribute("action", o);
        i.setAttribute("target", t.name);
        i.style.display = "none";
        document.body.appendChild(i);
        return i
    }

    var options = o, inputs = [], uuids = [], detachLoadEvents = {}, uploadComplete = uploadCompleteCallback, log = logCallback, api;
    api = {add: function (e) {
        e.setAttribute("name", options.inputName);
        var t = inputs.push(e) - 1;
        uuids[t] = qq.getUniqueId();
        if (e.parentNode) {
            qq(e).remove()
        }
        return t
    }, getName: function (e) {
        return inputs[e].value.replace(/.*(\/|\\)/, "")
    }, isValid: function (e) {
        return inputs[e] !== undefined
    }, reset: function () {
        qq.UploadHandler.prototype.reset.apply(this, arguments);
        inputs = [];
        uuids = [];
        detachLoadEvents = {}
    }, getUuid: function (e) {
        return uuids[e]
    }, cancel: function (e) {
        options.onCancel(e, this.getName(e));
        delete inputs[e];
        delete uuids[e];
        delete detachLoadEvents[e];
        var t = document.getElementById(e);
        if (t) {
            t.setAttribute("src", "java" + String.fromCharCode(115) + "cript:false;");
            qq(t).remove()
        }
    }, upload: function (e) {
        var t = inputs[e], n = api.getName(e), r = createIframe(e), i = createForm(e, r);
        if (!t) {
            throw new Error("file with passed id was not added, or already uploaded or cancelled")
        }
        options.onUpload(e, this.getName(e));
        i.appendChild(t);
        attachLoadEvent(r, function () {
            log("iframe loaded");
            var t = getIframeContentJson(r);
            setTimeout(function () {
                detachLoadEvents[e]();
                delete detachLoadEvents[e];
                qq(r).remove()
            }, 1);
            if (!t.success) {
                if (options.onAutoRetry(e, n, t)) {
                    return
                }
            }
            options.onComplete(e, n, t);
            uploadComplete(e)
        });
        log("Sending upload request for " + e);
        i.submit();
        qq(i).remove();
        return e
    }};
    return api
};
qq.UploadHandlerXhr = function (e, t, n) {
    "use strict";
    function p(e, t, n) {
        var i = h.getSize(e), s = h.getName(e);
        t[r.chunking.paramNames.partIndex] = n.part;
        t[r.chunking.paramNames.partByteOffset] = n.start;
        t[r.chunking.paramNames.chunkSize] = n.end - n.start;
        t[r.chunking.paramNames.totalParts] = n.count;
        t[r.totalFileSizeParamName] = i;
        if (c) {
            t[r.chunking.paramNames.filename] = s
        }
    }

    function d(e) {
        e[r.resume.paramNames.resuming] = true
    }

    function v(e, t, n) {
        if (e.slice) {
            return e.slice(t, n)
        } else if (e.mozSlice) {
            return e.mozSlice(t, n)
        } else if (e.webkitSlice) {
            return e.webkitSlice(t, n)
        }
    }

    function m(e, t) {
        var n = r.chunking.partSize, i = h.getSize(e), s = o[e].file, u = n * t, a = u + n >= i ? i : u + n, f = g(e);
        return{part: t, start: u, end: a, count: f, blob: v(s, u, a)}
    }

    function g(e) {
        var t = h.getSize(e), n = r.chunking.partSize;
        return Math.ceil(t / n)
    }

    function y(e) {
        o[e].xhr = new XMLHttpRequest;
        return o[e].xhr
    }

    function b(e, t, n, i) {
        var s = new FormData, u = r.demoMode ? "GET" : "POST", a = r.endpointStore.getEndpoint(i), f = a, l = h.getName(i), p = h.getSize(i);
        e[r.uuidParamName] = o[i].uuid;
        if (c) {
            e[r.totalFileSizeParamName] = p
        }
        if (!r.paramsInBody) {
            e[r.inputName] = l;
            f = qq.obj2url(e, a)
        }
        t.open(u, f, true);
        if (c) {
            if (r.paramsInBody) {
                qq.obj2FormData(e, s)
            }
            s.append(r.inputName, n);
            return s
        }
        return n
    }

    function w(e, t) {
        var n = r.customHeaders, i = h.getName(e), s = o[e].file;
        t.setRequestHeader("X-Requested-With", "XMLHttpRequest");
        t.setRequestHeader("Cache-Control", "no-cache");
        if (!c) {
            t.setRequestHeader("Content-Type", "application/octet-stream");
            t.setRequestHeader("X-Mime-Type", s.type)
        }
        qq.each(n, function (e, n) {
            t.setRequestHeader(e, n)
        })
    }

    function E(e, t, n) {
        var s = h.getName(e), u = h.getSize(e);
        o[e].attemptingResume = false;
        r.onProgress(e, s, u, u);
        r.onComplete(e, s, t, n);
        delete o[e].xhr;
        i(e)
    }

    function S(e) {
        var t = m(e, o[e].remainingChunkIdxs[0]), n = y(e), i = h.getSize(e), u = h.getName(e), a, f;
        if (o[e].loaded === undefined) {
            o[e].loaded = 0
        }
        _(e, t);
        n.onreadystatechange = M(e, n);
        n.upload.onprogress = function (t) {
            if (t.lengthComputable) {
                if (o[e].loaded < i) {
                    var n = t.loaded + o[e].loaded;
                    r.onProgress(e, u, n, i)
                }
            }
        };
        r.onUploadChunk(e, u, O(t));
        f = r.paramsStore.getParams(e);
        p(e, f, t);
        if (o[e].attemptingResume) {
            d(f)
        }
        a = b(f, n, t.blob, e);
        w(e, n);
        s("Sending chunked upload request for " + e + ": bytes " + (t.start + 1) + "-" + t.end + " of " + i);
        n.send(a)
    }

    function x(e, t, n) {
        var r = o[e].remainingChunkIdxs.shift(), i = m(e, r);
        o[e].attemptingResume = false;
        o[e].loaded += i.end - i.start;
        if (o[e].remainingChunkIdxs.length > 0) {
            S(e)
        } else {
            D(e);
            E(e, t, n)
        }
    }

    function T(e, t) {
        return e.status !== 200 || !t.success || t.reset
    }

    function N(e) {
        var t;
        try {
            t = qq.parseJson(e.responseText)
        } catch (n) {
            s("Error when attempting to parse xhr response text (" + n + ")", "error");
            t = {}
        }
        return t
    }

    function C(e) {
        s("Server has ordered chunking effort to be restarted on next attempt for file ID " + e, "error");
        if (f) {
            D(e)
        }
        o[e].remainingChunkIdxs = [];
        delete o[e].loaded
    }

    function k(e) {
        o[e].attemptingResume = false;
        s("Server has declared that it cannot handle resume for file ID " + e + " - starting from the first chunk", "error");
        h.upload(e, true)
    }

    function L(e, t, n) {
        var i = h.getName(e);
        if (r.onAutoRetry(e, i, t, n)) {
            return
        } else {
            E(e, t, n)
        }
    }

    function A(e, t) {
        var n;
        if (!o[e]) {
            return
        }
        s("xhr - server response received for " + e);
        s("responseText = " + t.responseText);
        n = N(t);
        if (T(t, n)) {
            if (n.reset) {
                C(e)
            }
            if (o[e].attemptingResume && n.reset) {
                k(e)
            } else {
                L(e, n, t)
            }
        } else if (a) {
            x(e, n, t)
        } else {
            E(e, n, t)
        }
    }

    function O(e) {
        return{partIndex: e.part, startByte: e.start + 1, endByte: e.end, totalParts: e.count}
    }

    function M(e, t) {
        return function () {
            if (t.readyState === 4) {
                A(e, t)
            }
        }
    }

    function _(e, t) {
        var n = h.getUuid(e), i = H(e), s = n + u + t.part, o = r.resume.cookiesExpireIn;
        qq.setCookie(i, s, o)
    }

    function D(e) {
        var t = H(e);
        qq.deleteCookie(t)
    }

    function P(e) {
        var t = qq.getCookie(H(e)), n, r, i;
        if (t) {
            n = t.indexOf(u);
            r = t.substr(0, n);
            i = parseInt(t.substr(n + 1, t.length - n), 10);
            return{uuid: r, part: i}
        }
    }

    function H(e) {
        var t = h.getName(e), n = h.getSize(e), i = r.chunking.partSize, s;
        s = "qqfilechunk" + u + encodeURIComponent(t) + u + n + u + i;
        if (l !== undefined) {
            s += u + l
        }
        return s
    }

    function B() {
        if (r.resume.id !== null && r.resume.id !== undefined && !qq.isFunction(r.resume.id) && !qq.isObject(r.resume.id)) {
            return r.resume.id
        }
    }

    function j(e, t) {
        var n = h.getName(e), i = 0, u, a, l;
        if (!o[e].remainingChunkIdxs || o[e].remainingChunkIdxs.length === 0) {
            o[e].remainingChunkIdxs = [];
            if (f && !t) {
                u = P(e);
                if (u) {
                    a = m(e, u.part);
                    if (r.onResume(e, n, O(a)) !== false) {
                        i = u.part;
                        o[e].uuid = u.uuid;
                        o[e].loaded = a.start;
                        o[e].attemptingResume = true;
                        s("Resuming " + n + " at partition index " + i)
                    }
                }
            }
            for (l = g(e) - 1; l >= i; l -= 1) {
                o[e].remainingChunkIdxs.unshift(l)
            }
        }
        S(e)
    }

    function F(e) {
        var t = o[e].file, n = h.getName(e), i, u, a;
        o[e].loaded = 0;
        i = y(e);
        i.upload.onprogress = function (t) {
            if (t.lengthComputable) {
                o[e].loaded = t.loaded;
                r.onProgress(e, n, t.loaded, t.total)
            }
        };
        i.onreadystatechange = M(e, i);
        u = r.paramsStore.getParams(e);
        a = b(u, i, t, e);
        w(e, i);
        s("Sending upload request for " + e);
        i.send(a)
    }

    var r = e, i = t, s = n, o = [], u = "|", a = r.chunking.enabled && qq.isFileChunkingSupported(), f = r.resume.enabled && a && qq.areCookiesEnabled(), l = B(), c = r.forceMultipart || r.paramsInBody, h;
    h = {add: function (e) {
        if (!(e instanceof File)) {
            throw new Error("Passed obj in not a File (in qq.UploadHandlerXhr)")
        }
        var t = o.push({file: e}) - 1;
        o[t].uuid = qq.getUniqueId();
        return t
    }, getName: function (e) {
        var t = o[e].file;
        return t.fileName !== null && t.fileName !== undefined ? t.fileName : t.name
    }, getSize: function (e) {
        var t = o[e].file;
        return t.fileSize != null ? t.fileSize : t.size
    }, getFile: function (e) {
        if (o[e]) {
            return o[e].file
        }
    }, getLoaded: function (e) {
        return o[e].loaded || 0
    }, isValid: function (e) {
        return o[e] !== undefined
    }, reset: function () {
        o = []
    }, getUuid: function (e) {
        return o[e].uuid
    }, upload: function (e, t) {
        var n = this.getName(e);
        r.onUpload(e, n);
        if (a) {
            j(e, t)
        } else {
            F(e)
        }
    }, cancel: function (e) {
        r.onCancel(e, this.getName(e));
        if (o[e].xhr) {
            o[e].xhr.abort()
        }
        if (f) {
            D(e)
        }
        delete o[e]
    }, getResumableFilesData: function () {
        var e = [], t = [];
        if (a && f) {
            if (l === undefined) {
                e = qq.getCookieNames(new RegExp("^qqfilechunk\\" + u + ".+\\" + u + "\\d+\\" + u + r.chunking.partSize + "="))
            } else {
                e = qq.getCookieNames(new RegExp("^qqfilechunk\\" + u + ".+\\" + u + "\\d+\\" + u + r.chunking.partSize + "\\" + u + l + "="))
            }
            qq.each(e, function (e, n) {
                var r = n.split(u);
                var i = qq.getCookie(n).split(u);
                t.push({name: decodeURIComponent(r[1]), size: r[2], uuid: i[0], partIdx: i[1]})
            });
            return t
        }
        return[]
    }};
    return h
};
qq.FineUploaderBasic = function (e) {
    var t = this;
    this._options = {debug: false, button: null, multiple: true, maxConnections: 3, disableCancelForFormUploads: false, autoUpload: true, request: {endpoint: "/server/upload", params: {}, paramsInBody: false, customHeaders: {}, forceMultipart: true, inputName: "qqfile", uuidName: "qquuid", totalFileSizeName: "qqtotalfilesize"}, validation: {allowedExtensions: [], sizeLimit: 0, minSizeLimit: 0, stopOnFirstInvalidFile: true}, callbacks: {onSubmit: function (e, t) {
    }, onComplete: function (e, t, n) {
    }, onCancel: function (e, t) {
    }, onUpload: function (e, t) {
    }, onUploadChunk: function (e, t, n) {
    }, onResume: function (e, t, n) {
    }, onProgress: function (e, t, n, r) {
    }, onError: function (e, t, n) {
    }, onAutoRetry: function (e, t, n) {
    }, onManualRetry: function (e, t) {
    }, onValidateBatch: function (e) {
    }, onValidate: function (e) {
    }}, messages: {typeError: "{file} has an invalid extension. Valid extension(s): {extensions}.", sizeError: "{file} is too large, maximum file size is {sizeLimit}.", minSizeError: "{file} is too small, minimum file size is {minSizeLimit}.", emptyError: "{file} is empty, please select files again without it.", noFilesError: "No files to upload.", onLeave: "The files are being uploaded, if you leave now the upload will be cancelled."}, retry: {enableAuto: false, maxAutoAttempts: 3, autoAttemptDelay: 5, preventRetryResponseProperty: "preventRetry"}, classes: {buttonHover: "qq-upload-button-hover", buttonFocus: "qq-upload-button-focus"}, chunking: {enabled: false, partSize: 2e6, paramNames: {partIndex: "qqpartindex", partByteOffset: "qqpartbyteoffset", chunkSize: "qqchunksize", totalFileSize: "qqtotalfilesize", totalParts: "qqtotalparts", filename: "qqfilename"}}, resume: {enabled: false, id: null, cookiesExpireIn: 7, paramNames: {resuming: "qqresume"}}, formatFileName: function (e) {
        if (e.length > 33) {
            e = e.slice(0, 19) + "..." + e.slice(-14)
        }
        return e
    }, text: {sizeSymbols: ["kB", "MB", "GB", "TB", "PB", "EB"]}};
    qq.extend(this._options, e, true);
    this._wrapCallbacks();
    this._disposeSupport = new qq.DisposeSupport;
    this._filesInProgress = [];
    this._storedFileIds = [];
    this._autoRetries = [];
    this._retryTimeouts = [];
    this._preventRetries = [];
    this._paramsStore = this._createParamsStore();
    this._endpointStore = this._createEndpointStore();
    this._handler = this._createUploadHandler();
    if (this._options.button) {
        this._button = this._createUploadButton(this._options.button)
    }
    this._preventLeaveInProgress()
};
qq.FineUploaderBasic.prototype = {log: function (e, t) {
    if (this._options.debug && (!t || t === "info")) {
        qq.log("[FineUploader] " + e)
    } else if (t && t !== "info") {
        qq.log("[FineUploader] " + e, t)
    }
}, setParams: function (e, t) {
    if (t == null) {
        this._options.request.params = e
    } else {
        this._paramsStore.setParams(e, t)
    }
}, setEndpoint: function (e, t) {
    if (t == null) {
        this._options.request.endpoint = e
    } else {
        this._endpointStore.setEndpoint(e, t)
    }
}, getInProgress: function () {
    return this._filesInProgress.length
}, uploadStoredFiles: function () {
    "use strict";
    var e;
    while (this._storedFileIds.length) {
        e = this._storedFileIds.shift();
        this._filesInProgress.push(e);
        this._handler.upload(e)
    }
}, clearStoredFiles: function () {
    this._storedFileIds = []
}, retry: function (e) {
    if (this._onBeforeManualRetry(e)) {
        this._handler.retry(e);
        return true
    } else {
        return false
    }
}, cancel: function (e) {
    this._handler.cancel(e)
}, reset: function () {
    this.log("Resetting uploader...");
    this._handler.reset();
    this._filesInProgress = [];
    this._storedFileIds = [];
    this._autoRetries = [];
    this._retryTimeouts = [];
    this._preventRetries = [];
    this._button.reset();
    this._paramsStore.reset();
    this._endpointStore.reset()
}, addFiles: function (e) {
    var t = this, n = [], r, i;
    if (e) {
        if (!window.FileList || !(e instanceof FileList)) {
            e = [].concat(e)
        }
        for (r = 0; r < e.length; r += 1) {
            i = e[r];
            if (qq.isFileOrInput(i)) {
                n.push(i)
            } else {
                t.log(i + " is not a File or INPUT element!  Ignoring!", "warn")
            }
        }
        this.log("Processing " + n.length + " files or inputs...");
        this._uploadFileList(n)
    }
}, getUuid: function (e) {
    return this._handler.getUuid(e)
}, getResumableFilesData: function () {
    return this._handler.getResumableFilesData()
}, getSize: function (e) {
    return this._handler.getSize(e)
}, getFile: function (e) {
    return this._handler.getFile(e)
}, _createUploadButton: function (e) {
    var t = this;
    var n = new qq.UploadButton({element: e, multiple: this._options.multiple && qq.isXhrUploadSupported(), acceptFiles: this._options.validation.acceptFiles, onChange: function (e) {
        t._onInputChange(e)
    }, hoverClass: this._options.classes.buttonHover, focusClass: this._options.classes.buttonFocus});
    this._disposeSupport.addDisposer(function () {
        n.dispose()
    });
    return n
}, _createUploadHandler: function () {
    var e = this;
    return new qq.UploadHandler({debug: this._options.debug, forceMultipart: this._options.request.forceMultipart, maxConnections: this._options.maxConnections, customHeaders: this._options.request.customHeaders, inputName: this._options.request.inputName, uuidParamName: this._options.request.uuidName, totalFileSizeParamName: this._options.request.totalFileSizeName, demoMode: this._options.demoMode, paramsInBody: this._options.request.paramsInBody, paramsStore: this._paramsStore, endpointStore: this._endpointStore, chunking: this._options.chunking, resume: this._options.resume, log: function (t, n) {
        e.log(t, n)
    }, onProgress: function (t, n, r, i) {
        e._onProgress(t, n, r, i);
        e._options.callbacks.onProgress(t, n, r, i)
    }, onComplete: function (t, n, r, i) {
        e._onComplete(t, n, r, i);
        e._options.callbacks.onComplete(t, n, r)
    }, onCancel: function (t, n) {
        e._onCancel(t, n);
        e._options.callbacks.onCancel(t, n)
    }, onUpload: function (t, n) {
        e._onUpload(t, n);
        e._options.callbacks.onUpload(t, n)
    }, onUploadChunk: function (t, n, r) {
        e._options.callbacks.onUploadChunk(t, n, r)
    }, onResume: function (t, n, r) {
        return e._options.callbacks.onResume(t, n, r)
    }, onAutoRetry: function (t, n, r, i) {
        e._preventRetries[t] = r[e._options.retry.preventRetryResponseProperty];
        if (e._shouldAutoRetry(t, n, r)) {
            e._maybeParseAndSendUploadError(t, n, r, i);
            e._options.callbacks.onAutoRetry(t, n, e._autoRetries[t] + 1);
            e._onBeforeAutoRetry(t, n);
            e._retryTimeouts[t] = setTimeout(function () {
                e._onAutoRetry(t, n, r)
            }, e._options.retry.autoAttemptDelay * 1e3);
            return true
        } else {
            return false
        }
    }})
}, _preventLeaveInProgress: function () {
    var e = this;
    this._disposeSupport.attach(window, "beforeunload", function (t) {
        if (!e._filesInProgress.length) {
            return
        }
        var t = t || window.event;
        t.returnValue = e._options.messages.onLeave;
        return e._options.messages.onLeave
    })
}, _onSubmit: function (e, t) {
    if (this._options.autoUpload) {
        this._filesInProgress.push(e)
    }
}, _onProgress: function (e, t, n, r) {
}, _onComplete: function (e, t, n, r) {
    this._removeFromFilesInProgress(e);
    this._maybeParseAndSendUploadError(e, t, n, r)
}, _onCancel: function (e, t) {
    this._removeFromFilesInProgress(e);
    clearTimeout(this._retryTimeouts[e]);
    var n = qq.indexOf(this._storedFileIds, e);
    if (!this._options.autoUpload && n >= 0) {
        this._storedFileIds.splice(n, 1)
    }
}, _removeFromFilesInProgress: function (e) {
    var t = qq.indexOf(this._filesInProgress, e);
    if (t >= 0) {
        this._filesInProgress.splice(t, 1)
    }
}, _onUpload: function (e, t) {
}, _onInputChange: function (e) {
    if (qq.isXhrUploadSupported()) {
        this.addFiles(e.files)
    } else {
        this.addFiles(e)
    }
    this._button.reset()
}, _onBeforeAutoRetry: function (e, t) {
    this.log("Waiting " + this._options.retry.autoAttemptDelay + " seconds before retrying " + t + "...")
}, _onAutoRetry: function (e, t, n) {
    this.log("Retrying " + t + "...");
    this._autoRetries[e]++;
    this._handler.retry(e)
}, _shouldAutoRetry: function (e, t, n) {
    if (!this._preventRetries[e] && this._options.retry.enableAuto) {
        if (this._autoRetries[e] === undefined) {
            this._autoRetries[e] = 0
        }
        return this._autoRetries[e] < this._options.retry.maxAutoAttempts
    }
    return false
}, _onBeforeManualRetry: function (e) {
    if (this._preventRetries[e]) {
        this.log("Retries are forbidden for id " + e, "warn");
        return false
    } else if (this._handler.isValid(e)) {
        var t = this._handler.getName(e);
        if (this._options.callbacks.onManualRetry(e, t) === false) {
            return false
        }
        this.log("Retrying upload for '" + t + "' (id: " + e + ")...");
        this._filesInProgress.push(e);
        return true
    } else {
        this.log("'" + e + "' is not a valid file ID", "error");
        return false
    }
}, _maybeParseAndSendUploadError: function (e, t, n, r) {
    if (!n.success) {
        if (r && r.status !== 200 && !n.error) {
            this._options.callbacks.onError(e, t, "XHR returned response code " + r.status)
        } else {
            var i = n.error ? n.error : "Upload failure reason unknown";
            this._options.callbacks.onError(e, t, i)
        }
    }
}, _uploadFileList: function (e) {
    var t, n, r;
    t = this._getValidationDescriptors(e);
    r = this._options.callbacks.onValidateBatch(t) === false;
    if (!r) {
        if (e.length > 0) {
            for (n = 0; n < e.length; n++) {
                if (this._validateFile(e[n])) {
                    this._uploadFile(e[n])
                } else {
                    if (this._options.validation.stopOnFirstInvalidFile) {
                        return
                    }
                }
            }
        } else {
            this._error("noFilesError", "")
        }
    }
}, _uploadFile: function (e) {
    var t = this._handler.add(e);
    var n = this._handler.getName(t);
    if (this._options.callbacks.onSubmit(t, n) !== false) {
        this._onSubmit(t, n);
        if (this._options.autoUpload) {
            this._handler.upload(t)
        } else {
            this._storeFileForLater(t)
        }
    }
}, _storeFileForLater: function (e) {
    this._storedFileIds.push(e)
}, _validateFile: function (e) {
    var t, n, r;
    t = this._getValidationDescriptor(e);
    n = t.name;
    r = t.size;
    if (this._options.callbacks.onValidate(t) === false) {
        return false
    }
    if (!this._isAllowedExtension(n)) {
        this._error("typeError", n);
        return false
    } else if (r === 0) {
        this._error("emptyError", n);
        return false
    } else if (r && this._options.validation.sizeLimit && r > this._options.validation.sizeLimit) {
        this._error("sizeError", n);
        return false
    } else if (r && r < this._options.validation.minSizeLimit) {
        this._error("minSizeError", n);
        return false
    }
    return true
}, _error: function (e, t) {
    function r(e, t) {
        n = n.replace(e, t)
    }

    var n = this._options.messages[e];
    var i = this._options.validation.allowedExtensions.join(", ").toLowerCase();
    r("{file}", this._options.formatFileName(t));
    r("{extensions}", i);
    r("{sizeLimit}", this._formatSize(this._options.validation.sizeLimit));
    r("{minSizeLimit}", this._formatSize(this._options.validation.minSizeLimit));
    this._options.callbacks.onError(null, t, n);
    return n
}, _isAllowedExtension: function (e) {
    var t = this._options.validation.allowedExtensions, n = false;
    if (!t.length) {
        return true
    }
    qq.each(t, function (t, r) {
        var i = new RegExp("\\." + r + "$", "i");
        if (e.match(i) != null) {
            n = true;
            return false
        }
    });
    return n
}, _formatSize: function (e) {
    var t = -1;
    do {
        e = e / 1024;
        t++
    } while (e > 99);
    return Math.max(e, .1).toFixed(1) + this._options.text.sizeSymbols[t]
}, _wrapCallbacks: function () {
    var e, t;
    e = this;
    t = function (t, n, r) {
        try {
            return n.apply(e, r)
        } catch (i) {
            e.log("Caught exception in '" + t + "' callback - " + i.message, "error")
        }
    };
    for (var n in this._options.callbacks) {
        (function () {
            var r, i;
            r = n;
            i = e._options.callbacks[r];
            e._options.callbacks[r] = function () {
                return t(r, i, arguments)
            }
        })()
    }
}, _parseFileName: function (e) {
    var t;
    if (e.value) {
        t = e.value.replace(/.*(\/|\\)/, "")
    } else {
        t = e.fileName !== null && e.fileName !== undefined ? e.fileName : e.name
    }
    return t
}, _parseFileSize: function (e) {
    var t;
    if (!e.value) {
        t = e.fileSize !== null && e.fileSize !== undefined ? e.fileSize : e.size
    }
    return t
}, _getValidationDescriptor: function (e) {
    var t, n, r;
    r = {};
    t = this._parseFileName(e);
    n = this._parseFileSize(e);
    r.name = t;
    if (n) {
        r.size = n
    }
    return r
}, _getValidationDescriptors: function (e) {
    var t = this, n = [];
    qq.each(e, function (e, r) {
        n.push(t._getValidationDescriptor(r))
    });
    return n
}, _createParamsStore: function () {
    var e = {}, t = this;
    return{setParams: function (t, n) {
        var r = {};
        qq.extend(r, t);
        e[n] = r
    }, getParams: function (n) {
        var r = {};
        if (n != null && e[n]) {
            qq.extend(r, e[n])
        } else {
            qq.extend(r, t._options.request.params)
        }
        return r
    }, remove: function (t) {
        return delete e[t]
    }, reset: function () {
        e = {}
    }}
}, _createEndpointStore: function () {
    var e = {}, t = this;
    return{setEndpoint: function (t, n) {
        e[n] = t
    }, getEndpoint: function (n) {
        if (n != null && e[n]) {
            return e[n]
        }
        return t._options.request.endpoint
    }, remove: function (t) {
        return delete e[t]
    }, reset: function () {
        e = {}
    }}
}};
qq.DragAndDrop = function (e) {
    "use strict";
    function a() {
        if (s === o && !r) {
            t.callbacks.log("Grabbed " + i.length + " files after tree traversal.");
            n.dropDisabled(false);
            t.callbacks.dropProcessing(false, i)
        }
    }

    function f(e) {
        i.push(e);
        o += 1;
        a()
    }

    function l(e) {
        var t, n;
        s += 1;
        if (e.isFile) {
            e.file(function (e) {
                f(e)
            })
        } else if (e.isDirectory) {
            r = true;
            t = e.createReader();
            t.readEntries(function (e) {
                o += 1;
                for (n = 0; n < e.length; n += 1) {
                    l(e[n])
                }
                r = false;
                if (!e.length) {
                    a()
                }
            })
        }
    }

    function c(e) {
        var r, u, f;
        t.callbacks.dropProcessing(true);
        n.dropDisabled(true);
        if (e.files.length > 1 && !t.multiple) {
            t.callbacks.dropProcessing(false);
            t.callbacks.error("tooManyFilesError", "");
            n.dropDisabled(false)
        } else {
            i = [];
            s = 0;
            o = 0;
            if (qq.isFolderDropSupported(e)) {
                u = e.items;
                for (r = 0; r < u.length; r += 1) {
                    f = u[r].webkitGetAsEntry();
                    if (f) {
                        if (f.isFile) {
                            i.push(u[r].getAsFile());
                            if (r === u.length - 1) {
                                a()
                            }
                        } else {
                            l(f)
                        }
                    }
                }
            } else {
                t.callbacks.dropProcessing(false, e.files);
                n.dropDisabled(false)
            }
        }
    }

    function h(e) {
        n = new qq.UploadDropZone({element: e, onEnter: function (n) {
            qq(e).addClass(t.classes.dropActive);
            n.stopPropagation()
        }, onLeaveNotDescendants: function (n) {
            qq(e).removeClass(t.classes.dropActive)
        }, onDrop: function (n) {
            if (t.hideDropzones) {
                qq(e).hide()
            }
            qq(e).removeClass(t.classes.dropActive);
            c(n.dataTransfer)
        }});
        u.addDisposer(function () {
            n.dispose()
        });
        if (t.hideDropzones) {
            qq(e).hide()
        }
    }

    function p(e) {
        var t;
        qq.each(e.dataTransfer.types, function (e, n) {
            if (n === "Files") {
                t = true;
                return false
            }
        });
        return t
    }

    function d() {
        if (t.dropArea) {
            t.extraDropzones.push(t.dropArea)
        }
        var e, r = t.extraDropzones;
        for (e = 0; e < r.length; e += 1) {
            h(r[e])
        }
        if (t.dropArea && (!qq.ie() || qq.ie10())) {
            u.attach(document, "dragenter", function (i) {
                if (!n.dropDisabled() && p(i)) {
                    if (qq(t.dropArea).hasClass(t.classes.dropDisabled)) {
                        return
                    }
                    t.dropArea.style.display = "block";
                    for (e = 0; e < r.length; e += 1) {
                        r[e].style.display = "block"
                    }
                }
            })
        }
        u.attach(document, "dragleave", function (n) {
            if (t.hideDropzones && qq.FineUploader.prototype._leaving_document_out(n)) {
                for (e = 0; e < r.length; e += 1) {
                    qq(r[e]).hide()
                }
            }
        });
        u.attach(document, "drop", function (n) {
            if (t.hideDropzones) {
                for (e = 0; e < r.length; e += 1) {
                    qq(r[e]).hide()
                }
            }
            n.preventDefault()
        })
    }

    var t, n, r, i = [], s = 0, o = 0, u = new qq.DisposeSupport;
    t = {dropArea: null, extraDropzones: [], hideDropzones: true, multiple: true, classes: {dropActive: null}, callbacks: {dropProcessing: function (e, t) {
    }, error: function (e, t) {
    }, log: function (e, t) {
    }}};
    qq.extend(t, e);
    return{setup: function () {
        d()
    }, setupExtraDropzone: function (e) {
        t.extraDropzones.push(e);
        h(e)
    }, removeExtraDropzone: function (e) {
        var n, r = t.extraDropzones;
        for (n in r) {
            if (r[n] === e) {
                return r.splice(n, 1)
            }
        }
    }, dispose: function () {
        u.dispose();
        n.dispose()
    }}
};
qq.UploadDropZone = function (e) {
    "use strict";
    function o() {
        return qq.safari() || qq.firefox() && qq.windows()
    }

    function u(e) {
        if (!i) {
            if (o) {
                s.attach(document, "dragover", function (e) {
                    e.preventDefault()
                })
            } else {
                s.attach(document, "dragover", function (e) {
                    if (e.dataTransfer) {
                        e.dataTransfer.dropEffect = "none";
                        e.preventDefault()
                    }
                })
            }
            i = true
        }
    }

    function a(e) {
        if (qq.ie() && !qq.ie10()) {
            return false
        }
        var t, n = e.dataTransfer, r = qq.safari();
        t = qq.ie10() ? true : n.effectAllowed !== "none";
        return n && t && (n.files || !r && n.types.contains && n.types.contains("Files"))
    }

    function f(e) {
        if (e !== undefined) {
            r = e
        }
        return r
    }

    function l() {
        s.attach(n, "dragover", function (e) {
            if (!a(e)) {
                return
            }
            var t = qq.ie() ? null : e.dataTransfer.effectAllowed;
            if (t === "move" || t === "linkMove") {
                e.dataTransfer.dropEffect = "move"
            } else {
                e.dataTransfer.dropEffect = "copy"
            }
            e.stopPropagation();
            e.preventDefault()
        });
        s.attach(n, "dragenter", function (e) {
            if (!f()) {
                if (!a(e)) {
                    return
                }
                t.onEnter(e)
            }
        });
        s.attach(n, "dragleave", function (e) {
            if (!a(e)) {
                return
            }
            t.onLeave(e);
            var n = document.elementFromPoint(e.clientX, e.clientY);
            if (qq(this).contains(n)) {
                return
            }
            t.onLeaveNotDescendants(e)
        });
        s.attach(n, "drop", function (e) {
            if (!f()) {
                if (!a(e)) {
                    return
                }
                e.preventDefault();
                t.onDrop(e)
            }
        })
    }

    var t, n, r, i, s = new qq.DisposeSupport;
    t = {element: null, onEnter: function (e) {
    }, onLeave: function (e) {
    }, onLeaveNotDescendants: function (e) {
    }, onDrop: function (e) {
    }};
    qq.extend(t, e);
    n = t.element;
    u();
    l();
    return{dropDisabled: function (e) {
        return f(e)
    }, dispose: function () {
        s.dispose()
    }}
};
qq.FineUploader = function (e) {
    qq.FineUploaderBasic.apply(this, arguments);
    qq.extend(this._options, {element: null, listElement: null, dragAndDrop: {extraDropzones: [], hideDropzones: true, disableDefaultDropzone: false}, text: {uploadButton: "Upload a file", cancelButton: "Cancel", retryButton: "Retry", failUpload: "Upload failed", dragZone: "Drop files here to upload", dropProcessing: "Processing dropped files...", formatProgress: "{percent}% of {total_size}", waitingForResponse: "Processing..."}, template: '<div class="qq-uploader">' + (!this._options.dragAndDrop || !this._options.dragAndDrop.disableDefaultDropzone ? '<div class="qq-upload-drop-area"><span>{dragZoneText}</span></div>' : "") + (!this._options.button ? '<div class="qq-upload-button"><div>{uploadButtonText}</div></div>' : "") + '<span class="qq-drop-processing"><span>{dropProcessingText}</span><span class="qq-drop-processing-spinner"></span></span>' + (!this._options.listElement ? '<ul class="qq-upload-list"></ul>' : "") + "</div>", fileTemplate: "<li>" + '<div class="qq-progress-bar"></div>' + '<span class="qq-upload-spinner"></span>' + '<span class="qq-upload-finished"></span>' + '<span class="qq-upload-file"></span>' + '<span class="qq-upload-size"></span>' + '<a class="qq-upload-cancel" href="#">{cancelButtonText}</a>' + '<a class="qq-upload-retry" href="#">{retryButtonText}</a>' + '<span class="qq-upload-status-text">{statusText}</span>' + "</li>", classes: {button: "qq-upload-button", drop: "qq-upload-drop-area", dropActive: "qq-upload-drop-area-active", dropDisabled: "qq-upload-drop-area-disabled", list: "qq-upload-list", progressBar: "qq-progress-bar", file: "qq-upload-file", spinner: "qq-upload-spinner", finished: "qq-upload-finished", retrying: "qq-upload-retrying", retryable: "qq-upload-retryable", size: "qq-upload-size", cancel: "qq-upload-cancel", retry: "qq-upload-retry", statusText: "qq-upload-status-text", success: "qq-upload-success", fail: "qq-upload-fail", successIcon: null, failIcon: null, dropProcessing: "qq-drop-processing", dropProcessingSpinner: "qq-drop-processing-spinner"}, failedUploadTextDisplay: {mode: "default", maxChars: 50, responseProperty: "error", enableTooltip: true}, messages: {tooManyFilesError: "You may only drop one file"}, retry: {showAutoRetryNote: true, autoRetryNote: "Retrying {retryNum}/{maxAuto}...", showButton: false}, showMessage: function (e) {
        setTimeout(function () {
            alert(e)
        }, 0)
    }}, true);
    qq.extend(this._options, e, true);
    this._wrapCallbacks();
    this._options.template = this._options.template.replace(/\{dragZoneText\}/g, this._options.text.dragZone);
    this._options.template = this._options.template.replace(/\{uploadButtonText\}/g, this._options.text.uploadButton);
    this._options.template = this._options.template.replace(/\{dropProcessingText\}/g, this._options.text.dropProcessing);
    this._options.fileTemplate = this._options.fileTemplate.replace(/\{cancelButtonText\}/g, this._options.text.cancelButton);
    this._options.fileTemplate = this._options.fileTemplate.replace(/\{retryButtonText\}/g, this._options.text.retryButton);
    this._options.fileTemplate = this._options.fileTemplate.replace(/\{statusText\}/g, "");
    this._element = this._options.element;
    this._element.innerHTML = this._options.template;
    this._listElement = this._options.listElement || this._find(this._element, "list");
    this._classes = this._options.classes;
    if (!this._button) {
        this._button = this._createUploadButton(this._find(this._element, "button"))
    }
    this._bindCancelAndRetryEvents();
    this._dnd = this._setupDragAndDrop()
};
qq.extend(qq.FineUploader.prototype, qq.FineUploaderBasic.prototype);
qq.extend(qq.FineUploader.prototype, {clearStoredFiles: function () {
    qq.FineUploaderBasic.prototype.clearStoredFiles.apply(this, arguments);
    this._listElement.innerHTML = ""
}, addExtraDropzone: function (e) {
    this._dnd.setupExtraDropzone(e)
}, removeExtraDropzone: function (e) {
    return this._dnd.removeExtraDropzone(e)
}, getItemByFileId: function (e) {
    var t = this._listElement.firstChild;
    while (t) {
        if (t.qqFileId == e)return t;
        t = t.nextSibling
    }
}, cancel: function (e) {
    qq.FineUploaderBasic.prototype.cancel.apply(this, arguments);
    var t = this.getItemByFileId(e);
    qq(t).remove()
}, reset: function () {
    qq.FineUploaderBasic.prototype.reset.apply(this, arguments);
    this._element.innerHTML = this._options.template;
    this._listElement = this._options.listElement || this._find(this._element, "list");
    if (!this._options.button) {
        this._button = this._createUploadButton(this._find(this._element, "button"))
    }
    this._bindCancelAndRetryEvents();
    this._dnd.dispose();
    this._dnd = this._setupDragAndDrop()
}, _setupDragAndDrop: function () {
    var e = this, t = this._find(this._element, "dropProcessing"), n, r, i;
    r = function (e) {
        e.preventDefault()
    };
    if (!this._options.dragAndDrop.disableDefaultDropzone) {
        i = this._find(this._options.element, "drop")
    }
    n = new qq.DragAndDrop({dropArea: i, extraDropzones: this._options.dragAndDrop.extraDropzones, hideDropzones: this._options.dragAndDrop.hideDropzones, multiple: this._options.multiple, classes: {dropActive: this._options.classes.dropActive}, callbacks: {dropProcessing: function (n, i) {
        var s = e._button.getInput();
        if (n) {
            qq(t).css({display: "block"});
            qq(s).attach("click", r)
        } else {
            qq(t).hide();
            qq(s).detach("click", r)
        }
        if (i) {
            e.addFiles(i)
        }
    }, error: function (t, n) {
        e._error(t, n)
    }, log: function (t, n) {
        e.log(t, n)
    }}});
    n.setup();
    return n
}, _leaving_document_out: function (e) {
    return(qq.chrome() || qq.safari() && qq.windows()) && e.clientX == 0 && e.clientY == 0 || qq.firefox() && !e.relatedTarget
}, _storeFileForLater: function (e) {
    qq.FineUploaderBasic.prototype._storeFileForLater.apply(this, arguments);
    var t = this.getItemByFileId(e);
    qq(this._find(t, "spinner")).hide()
}, _find: function (e, t) {
    var n = qq(e).getByClass(this._options.classes[t])[0];
    if (!n) {
        throw new Error("element not found " + t)
    }
    return n
}, _onSubmit: function (e, t) {
    qq.FineUploaderBasic.prototype._onSubmit.apply(this, arguments);
    this._addToList(e, t)
}, _onProgress: function (e, t, n, r) {
    qq.FineUploaderBasic.prototype._onProgress.apply(this, arguments);
    var i, s, o, u, a, f;
    i = this.getItemByFileId(e);
    s = this._find(i, "progressBar");
    u = Math.round(n / r * 100);
    if (n === r) {
        a = this._find(i, "cancel");
        qq(a).hide();
        qq(s).hide();
        qq(this._find(i, "statusText")).setText(this._options.text.waitingForResponse);
        o = this._formatSize(r)
    } else {
        o = this._formatProgress(n, r);
        qq(s).css({display: "block"})
    }
    qq(s).css({width: u + "%"});
    f = this._find(i, "size");
    qq(f).css({display: "inline"});
    qq(f).setText(o)
}, _onComplete: function (e, t, n, r) {
    qq.FineUploaderBasic.prototype._onComplete.apply(this, arguments);
    var i = this.getItemByFileId(e);
    qq(this._find(i, "statusText")).clearText();
    qq(i).removeClass(this._classes.retrying);
    qq(this._find(i, "progressBar")).hide();
    if (!this._options.disableCancelForFormUploads || qq.isXhrUploadSupported()) {
        qq(this._find(i, "cancel")).hide()
    }
    qq(this._find(i, "spinner")).hide();
    if (n.success) {
        qq(i).addClass(this._classes.success);
        if (this._classes.successIcon) {
            this._find(i, "finished").style.display = "inline-block";
            qq(i).addClass(this._classes.successIcon)
        }
    } else {
        qq(i).addClass(this._classes.fail);
        if (this._classes.failIcon) {
            this._find(i, "finished").style.display = "inline-block";
            qq(i).addClass(this._classes.failIcon)
        }
        if (this._options.retry.showButton && !this._preventRetries[e]) {
            qq(i).addClass(this._classes.retryable)
        }
        this._controlFailureTextDisplay(i, n)
    }
}, _onUpload: function (e, t) {
    qq.FineUploaderBasic.prototype._onUpload.apply(this, arguments);
    var n = this.getItemByFileId(e);
    this._showSpinner(n)
}, _onBeforeAutoRetry: function (e) {
    var t, n, r, i, s, o, u;
    qq.FineUploaderBasic.prototype._onBeforeAutoRetry.apply(this, arguments);
    t = this.getItemByFileId(e);
    n = this._find(t, "progressBar");
    this._showCancelLink(t);
    n.style.width = 0;
    qq(n).hide();
    if (this._options.retry.showAutoRetryNote) {
        i = this._find(t, "statusText");
        s = this._autoRetries[e] + 1;
        o = this._options.retry.maxAutoAttempts;
        u = this._options.retry.autoRetryNote.replace(/\{retryNum\}/g, s);
        u = u.replace(/\{maxAuto\}/g, o);
        qq(i).setText(u);
        if (s === 1) {
            qq(t).addClass(this._classes.retrying)
        }
    }
}, _onBeforeManualRetry: function (e) {
    if (qq.FineUploaderBasic.prototype._onBeforeManualRetry.apply(this, arguments)) {
        var t = this.getItemByFileId(e);
        this._find(t, "progressBar").style.width = 0;
        qq(t).removeClass(this._classes.fail);
        qq(this._find(t, "statusText")).clearText();
        this._showSpinner(t);
        this._showCancelLink(t);
        return true
    }
    return false
}, _addToList: function (e, t) {
    var n = qq.toElement(this._options.fileTemplate);
    if (this._options.disableCancelForFormUploads && !qq.isXhrUploadSupported()) {
        var r = this._find(n, "cancel");
        qq(r).remove()
    }
    n.qqFileId = e;
    var i = this._find(n, "file");
    qq(i).setText(this._options.formatFileName(t));
    qq(this._find(n, "size")).hide();
    if (!this._options.multiple)this._clearList();
    this._listElement.appendChild(n)
}, _clearList: function () {
    this._listElement.innerHTML = "";
    this.clearStoredFiles()
}, _bindCancelAndRetryEvents: function () {
    var e = this, t = this._listElement;
    this._disposeSupport.attach(t, "click", function (t) {
        t = t || window.event;
        var n = t.target || t.srcElement;
        if (qq(n).hasClass(e._classes.cancel) || qq(n).hasClass(e._classes.retry)) {
            qq.preventDefault(t);
            var r = n.parentNode;
            while (r.qqFileId == undefined) {
                r = n = n.parentNode
            }
            if (qq(n).hasClass(e._classes.cancel)) {
                e.cancel(r.qqFileId)
            } else {
                qq(r).removeClass(e._classes.retryable);
                e.retry(r.qqFileId)
            }
        }
    })
}, _formatProgress: function (e, t) {
    function r(e, t) {
        n = n.replace(e, t)
    }

    var n = this._options.text.formatProgress;
    r("{percent}", Math.round(e / t * 100));
    r("{total_size}", this._formatSize(t));
    return n
}, _controlFailureTextDisplay: function (e, t) {
    var n, r, i, s, o;
    n = this._options.failedUploadTextDisplay.mode;
    r = this._options.failedUploadTextDisplay.maxChars;
    i = this._options.failedUploadTextDisplay.responseProperty;
    if (n === "custom") {
        s = t[i];
        if (s) {
            if (s.length > r) {
                o = s.substring(0, r) + "..."
            }
        } else {
            s = this._options.text.failUpload;
            this.log("'" + i + "' is not a valid property on the server response.", "warn")
        }
        qq(this._find(e, "statusText")).setText(o || s);
        if (this._options.failedUploadTextDisplay.enableTooltip) {
            this._showTooltip(e, s)
        }
    } else if (n === "default") {
        qq(this._find(e, "statusText")).setText(this._options.text.failUpload)
    } else if (n !== "none") {
        this.log("failedUploadTextDisplay.mode value of '" + n + "' is not valid", "warn")
    }
}, _showTooltip: function (e, t) {
    e.title = t
}, _showSpinner: function (e) {
    var t = this._find(e, "spinner");
    t.style.display = "inline-block"
}, _showCancelLink: function (e) {
    if (!this._options.disableCancelForFormUploads || qq.isXhrUploadSupported()) {
        var t = this._find(e, "cancel");
        t.style.display = "inline"
    }
}, _error: function (e, t) {
    var n = qq.FineUploaderBasic.prototype._error.apply(this, arguments);
    this._options.showMessage(n)
}});
(function (e) {
    "use strict";
    var t, n, r, i, s, o, u, a, f, l;
    o = ["uploaderType"];
    r = function (e) {
        if (e) {
            var r = a(e);
            u(r);
            if (s("uploaderType") === "basic") {
                t(new qq.FineUploaderBasic(r))
            } else {
                t(new qq.FineUploader(r))
            }
        }
        return n
    };
    i = function (e, t) {
        var r = n.data("fineuploader");
        if (t) {
            if (r === undefined) {
                r = {}
            }
            r[e] = t;
            n.data("fineuploader", r)
        } else {
            if (r === undefined) {
                return null
            }
            return r[e]
        }
    };
    t = function (e) {
        return i("uploader", e)
    };
    s = function (e, t) {
        return i(e, t)
    };
    u = function (t) {
        var r = t.callbacks = {};
        e.each((new qq.FineUploaderBasic)._options.callbacks, function (e, t) {
            var i, s;
            i = /^on(\w+)/.exec(e)[1];
            i = i.substring(0, 1).toLowerCase() + i.substring(1);
            s = n;
            r[e] = function () {
                var e = Array.prototype.slice.call(arguments);
                return s.triggerHandler(i, e)
            }
        })
    };
    a = function (t, r) {
        var i, u;
        if (r === undefined) {
            if (t.uploaderType !== "basic") {
                i = {element: n[0]}
            } else {
                i = {}
            }
        } else {
            i = r
        }
        e.each(t, function (t, n) {
            if (e.inArray(t, o) >= 0) {
                s(t, n)
            } else if (n instanceof e) {
                i[t] = n[0]
            } else if (e.isPlainObject(n)) {
                i[t] = {};
                a(n, i[t])
            } else if (e.isArray(n)) {
                u = [];
                e.each(n, function (t, n) {
                    if (n instanceof e) {
                        e.merge(u, n)
                    } else {
                        u.push(n)
                    }
                });
                i[t] = u
            } else {
                i[t] = n
            }
        });
        if (r === undefined) {
            return i
        }
    };
    f = function (n) {
        return e.type(n) === "string" && !n.match(/^_/) && t()[n] !== undefined
    };
    l = function (e) {
        var n = [], r = Array.prototype.slice.call(arguments, 1);
        a(r, n);
        return t()[e].apply(t(), n)
    };
    e.fn.fineUploader = function (i) {
        var s = this, o = arguments, u = [];
        this.each(function (a, c) {
            n = e(c);
            if (t() && f(i)) {
                u.push(l.apply(s, o));
                if (s.length === 1) {
                    return false
                }
            } else if (typeof i === "object" || !i) {
                r.apply(s, o)
            } else {
                e.error("Method " + i + " does not exist on jQuery.fineUploader")
            }
        });
        if (u.length === 1) {
            return u[0]
        } else if (u.length > 1) {
            return u
        }
        return this
    }
})(jQuery)