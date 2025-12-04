/******/ (() => { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./node_modules/core-js/internals/a-callable.js":
/*!******************************************************!*\
  !*** ./node_modules/core-js/internals/a-callable.js ***!
  \******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var tryToString = __webpack_require__(/*! ../internals/try-to-string */ "./node_modules/core-js/internals/try-to-string.js");

var $TypeError = TypeError;

// `Assert: IsCallable(argument) is true`
module.exports = function (argument) {
  if (isCallable(argument)) return argument;
  throw new $TypeError(tryToString(argument) + ' is not a function');
};


/***/ }),

/***/ "./node_modules/core-js/internals/a-constructor.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/a-constructor.js ***!
  \*********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isConstructor = __webpack_require__(/*! ../internals/is-constructor */ "./node_modules/core-js/internals/is-constructor.js");
var tryToString = __webpack_require__(/*! ../internals/try-to-string */ "./node_modules/core-js/internals/try-to-string.js");

var $TypeError = TypeError;

// `Assert: IsConstructor(argument) is true`
module.exports = function (argument) {
  if (isConstructor(argument)) return argument;
  throw new $TypeError(tryToString(argument) + ' is not a constructor');
};


/***/ }),

/***/ "./node_modules/core-js/internals/a-possible-prototype.js":
/*!****************************************************************!*\
  !*** ./node_modules/core-js/internals/a-possible-prototype.js ***!
  \****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isPossiblePrototype = __webpack_require__(/*! ../internals/is-possible-prototype */ "./node_modules/core-js/internals/is-possible-prototype.js");

var $String = String;
var $TypeError = TypeError;

module.exports = function (argument) {
  if (isPossiblePrototype(argument)) return argument;
  throw new $TypeError("Can't set " + $String(argument) + ' as a prototype');
};


/***/ }),

/***/ "./node_modules/core-js/internals/add-to-unscopables.js":
/*!**************************************************************!*\
  !*** ./node_modules/core-js/internals/add-to-unscopables.js ***!
  \**************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");
var create = __webpack_require__(/*! ../internals/object-create */ "./node_modules/core-js/internals/object-create.js");
var defineProperty = (__webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js").f);

var UNSCOPABLES = wellKnownSymbol('unscopables');
var ArrayPrototype = Array.prototype;

// Array.prototype[@@unscopables]
// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
if (ArrayPrototype[UNSCOPABLES] === undefined) {
  defineProperty(ArrayPrototype, UNSCOPABLES, {
    configurable: true,
    value: create(null)
  });
}

// add a key to Array.prototype[@@unscopables]
module.exports = function (key) {
  ArrayPrototype[UNSCOPABLES][key] = true;
};


/***/ }),

/***/ "./node_modules/core-js/internals/advance-string-index.js":
/*!****************************************************************!*\
  !*** ./node_modules/core-js/internals/advance-string-index.js ***!
  \****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var charAt = (__webpack_require__(/*! ../internals/string-multibyte */ "./node_modules/core-js/internals/string-multibyte.js").charAt);

// `AdvanceStringIndex` abstract operation
// https://tc39.es/ecma262/#sec-advancestringindex
module.exports = function (S, index, unicode) {
  return index + (unicode ? charAt(S, index).length : 1);
};


/***/ }),

/***/ "./node_modules/core-js/internals/an-object.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/an-object.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");

var $String = String;
var $TypeError = TypeError;

// `Assert: Type(argument) is Object`
module.exports = function (argument) {
  if (isObject(argument)) return argument;
  throw new $TypeError($String(argument) + ' is not an object');
};


/***/ }),

/***/ "./node_modules/core-js/internals/array-includes.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/array-includes.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "./node_modules/core-js/internals/to-indexed-object.js");
var toAbsoluteIndex = __webpack_require__(/*! ../internals/to-absolute-index */ "./node_modules/core-js/internals/to-absolute-index.js");
var lengthOfArrayLike = __webpack_require__(/*! ../internals/length-of-array-like */ "./node_modules/core-js/internals/length-of-array-like.js");

// `Array.prototype.{ indexOf, includes }` methods implementation
var createMethod = function (IS_INCLUDES) {
  return function ($this, el, fromIndex) {
    var O = toIndexedObject($this);
    var length = lengthOfArrayLike(O);
    if (length === 0) return !IS_INCLUDES && -1;
    var index = toAbsoluteIndex(fromIndex, length);
    var value;
    // Array#includes uses SameValueZero equality algorithm
    // eslint-disable-next-line no-self-compare -- NaN check
    if (IS_INCLUDES && el !== el) while (length > index) {
      value = O[index++];
      // eslint-disable-next-line no-self-compare -- NaN check
      if (value !== value) return true;
    // Array#indexOf ignores holes, Array#includes - not
    } else for (;length > index; index++) {
      if ((IS_INCLUDES || index in O) && O[index] === el) return IS_INCLUDES || index || 0;
    } return !IS_INCLUDES && -1;
  };
};

module.exports = {
  // `Array.prototype.includes` method
  // https://tc39.es/ecma262/#sec-array.prototype.includes
  includes: createMethod(true),
  // `Array.prototype.indexOf` method
  // https://tc39.es/ecma262/#sec-array.prototype.indexof
  indexOf: createMethod(false)
};


/***/ }),

/***/ "./node_modules/core-js/internals/classof-raw.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/classof-raw.js ***!
  \*******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");

var toString = uncurryThis({}.toString);
var stringSlice = uncurryThis(''.slice);

module.exports = function (it) {
  return stringSlice(toString(it), 8, -1);
};


/***/ }),

/***/ "./node_modules/core-js/internals/classof.js":
/*!***************************************************!*\
  !*** ./node_modules/core-js/internals/classof.js ***!
  \***************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var TO_STRING_TAG_SUPPORT = __webpack_require__(/*! ../internals/to-string-tag-support */ "./node_modules/core-js/internals/to-string-tag-support.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var classofRaw = __webpack_require__(/*! ../internals/classof-raw */ "./node_modules/core-js/internals/classof-raw.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var TO_STRING_TAG = wellKnownSymbol('toStringTag');
var $Object = Object;

// ES3 wrong here
var CORRECT_ARGUMENTS = classofRaw(function () { return arguments; }()) === 'Arguments';

// fallback for IE11 Script Access Denied error
var tryGet = function (it, key) {
  try {
    return it[key];
  } catch (error) { /* empty */ }
};

// getting tag from ES6+ `Object.prototype.toString`
module.exports = TO_STRING_TAG_SUPPORT ? classofRaw : function (it) {
  var O, tag, result;
  return it === undefined ? 'Undefined' : it === null ? 'Null'
    // @@toStringTag case
    : typeof (tag = tryGet(O = $Object(it), TO_STRING_TAG)) == 'string' ? tag
    // builtinTag case
    : CORRECT_ARGUMENTS ? classofRaw(O)
    // ES3 arguments fallback
    : (result = classofRaw(O)) === 'Object' && isCallable(O.callee) ? 'Arguments' : result;
};


/***/ }),

/***/ "./node_modules/core-js/internals/copy-constructor-properties.js":
/*!***********************************************************************!*\
  !*** ./node_modules/core-js/internals/copy-constructor-properties.js ***!
  \***********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var ownKeys = __webpack_require__(/*! ../internals/own-keys */ "./node_modules/core-js/internals/own-keys.js");
var getOwnPropertyDescriptorModule = __webpack_require__(/*! ../internals/object-get-own-property-descriptor */ "./node_modules/core-js/internals/object-get-own-property-descriptor.js");
var definePropertyModule = __webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js");

module.exports = function (target, source, exceptions) {
  var keys = ownKeys(source);
  var defineProperty = definePropertyModule.f;
  var getOwnPropertyDescriptor = getOwnPropertyDescriptorModule.f;
  for (var i = 0; i < keys.length; i++) {
    var key = keys[i];
    if (!hasOwn(target, key) && !(exceptions && hasOwn(exceptions, key))) {
      defineProperty(target, key, getOwnPropertyDescriptor(source, key));
    }
  }
};


/***/ }),

/***/ "./node_modules/core-js/internals/correct-prototype-getter.js":
/*!********************************************************************!*\
  !*** ./node_modules/core-js/internals/correct-prototype-getter.js ***!
  \********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");

module.exports = !fails(function () {
  function F() { /* empty */ }
  F.prototype.constructor = null;
  // eslint-disable-next-line es/no-object-getprototypeof -- required for testing
  return Object.getPrototypeOf(new F()) !== F.prototype;
});


/***/ }),

/***/ "./node_modules/core-js/internals/create-iter-result-object.js":
/*!*********************************************************************!*\
  !*** ./node_modules/core-js/internals/create-iter-result-object.js ***!
  \*********************************************************************/
/***/ ((module) => {

"use strict";

// `CreateIterResultObject` abstract operation
// https://tc39.es/ecma262/#sec-createiterresultobject
module.exports = function (value, done) {
  return { value: value, done: done };
};


/***/ }),

/***/ "./node_modules/core-js/internals/create-non-enumerable-property.js":
/*!**************************************************************************!*\
  !*** ./node_modules/core-js/internals/create-non-enumerable-property.js ***!
  \**************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var definePropertyModule = __webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js");
var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "./node_modules/core-js/internals/create-property-descriptor.js");

module.exports = DESCRIPTORS ? function (object, key, value) {
  return definePropertyModule.f(object, key, createPropertyDescriptor(1, value));
} : function (object, key, value) {
  object[key] = value;
  return object;
};


/***/ }),

/***/ "./node_modules/core-js/internals/create-property-descriptor.js":
/*!**********************************************************************!*\
  !*** ./node_modules/core-js/internals/create-property-descriptor.js ***!
  \**********************************************************************/
/***/ ((module) => {

"use strict";

module.exports = function (bitmap, value) {
  return {
    enumerable: !(bitmap & 1),
    configurable: !(bitmap & 2),
    writable: !(bitmap & 4),
    value: value
  };
};


/***/ }),

/***/ "./node_modules/core-js/internals/define-built-in-accessor.js":
/*!********************************************************************!*\
  !*** ./node_modules/core-js/internals/define-built-in-accessor.js ***!
  \********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var makeBuiltIn = __webpack_require__(/*! ../internals/make-built-in */ "./node_modules/core-js/internals/make-built-in.js");
var defineProperty = __webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js");

module.exports = function (target, name, descriptor) {
  if (descriptor.get) makeBuiltIn(descriptor.get, name, { getter: true });
  if (descriptor.set) makeBuiltIn(descriptor.set, name, { setter: true });
  return defineProperty.f(target, name, descriptor);
};


/***/ }),

/***/ "./node_modules/core-js/internals/define-built-in.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/internals/define-built-in.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var definePropertyModule = __webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js");
var makeBuiltIn = __webpack_require__(/*! ../internals/make-built-in */ "./node_modules/core-js/internals/make-built-in.js");
var defineGlobalProperty = __webpack_require__(/*! ../internals/define-global-property */ "./node_modules/core-js/internals/define-global-property.js");

module.exports = function (O, key, value, options) {
  if (!options) options = {};
  var simple = options.enumerable;
  var name = options.name !== undefined ? options.name : key;
  if (isCallable(value)) makeBuiltIn(value, name, options);
  if (options.global) {
    if (simple) O[key] = value;
    else defineGlobalProperty(key, value);
  } else {
    try {
      if (!options.unsafe) delete O[key];
      else if (O[key]) simple = true;
    } catch (error) { /* empty */ }
    if (simple) O[key] = value;
    else definePropertyModule.f(O, key, {
      value: value,
      enumerable: false,
      configurable: !options.nonConfigurable,
      writable: !options.nonWritable
    });
  } return O;
};


/***/ }),

/***/ "./node_modules/core-js/internals/define-global-property.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/define-global-property.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");

// eslint-disable-next-line es/no-object-defineproperty -- safe
var defineProperty = Object.defineProperty;

module.exports = function (key, value) {
  try {
    defineProperty(globalThis, key, { value: value, configurable: true, writable: true });
  } catch (error) {
    globalThis[key] = value;
  } return value;
};


/***/ }),

/***/ "./node_modules/core-js/internals/descriptors.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/descriptors.js ***!
  \*******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");

// Detect IE8's incomplete defineProperty implementation
module.exports = !fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  return Object.defineProperty({}, 1, { get: function () { return 7; } })[1] !== 7;
});


/***/ }),

/***/ "./node_modules/core-js/internals/document-create-element.js":
/*!*******************************************************************!*\
  !*** ./node_modules/core-js/internals/document-create-element.js ***!
  \*******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");

var document = globalThis.document;
// typeof document.createElement is 'object' in old IE
var EXISTS = isObject(document) && isObject(document.createElement);

module.exports = function (it) {
  return EXISTS ? document.createElement(it) : {};
};


/***/ }),

/***/ "./node_modules/core-js/internals/dom-iterables.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/dom-iterables.js ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";

// iterable DOM collections
// flag - `iterable` interface - 'entries', 'keys', 'values', 'forEach' methods
module.exports = {
  CSSRuleList: 0,
  CSSStyleDeclaration: 0,
  CSSValueList: 0,
  ClientRectList: 0,
  DOMRectList: 0,
  DOMStringList: 0,
  DOMTokenList: 1,
  DataTransferItemList: 0,
  FileList: 0,
  HTMLAllCollection: 0,
  HTMLCollection: 0,
  HTMLFormElement: 0,
  HTMLSelectElement: 0,
  MediaList: 0,
  MimeTypeArray: 0,
  NamedNodeMap: 0,
  NodeList: 1,
  PaintRequestList: 0,
  Plugin: 0,
  PluginArray: 0,
  SVGLengthList: 0,
  SVGNumberList: 0,
  SVGPathSegList: 0,
  SVGPointList: 0,
  SVGStringList: 0,
  SVGTransformList: 0,
  SourceBufferList: 0,
  StyleSheetList: 0,
  TextTrackCueList: 0,
  TextTrackList: 0,
  TouchList: 0
};


/***/ }),

/***/ "./node_modules/core-js/internals/dom-token-list-prototype.js":
/*!********************************************************************!*\
  !*** ./node_modules/core-js/internals/dom-token-list-prototype.js ***!
  \********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

// in old WebKit versions, `element.classList` is not an instance of global `DOMTokenList`
var documentCreateElement = __webpack_require__(/*! ../internals/document-create-element */ "./node_modules/core-js/internals/document-create-element.js");

var classList = documentCreateElement('span').classList;
var DOMTokenListPrototype = classList && classList.constructor && classList.constructor.prototype;

module.exports = DOMTokenListPrototype === Object.prototype ? undefined : DOMTokenListPrototype;


/***/ }),

/***/ "./node_modules/core-js/internals/enum-bug-keys.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/enum-bug-keys.js ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";

// IE8- don't enum bug keys
module.exports = [
  'constructor',
  'hasOwnProperty',
  'isPrototypeOf',
  'propertyIsEnumerable',
  'toLocaleString',
  'toString',
  'valueOf'
];


/***/ }),

/***/ "./node_modules/core-js/internals/environment-user-agent.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/environment-user-agent.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");

var navigator = globalThis.navigator;
var userAgent = navigator && navigator.userAgent;

module.exports = userAgent ? String(userAgent) : '';


/***/ }),

/***/ "./node_modules/core-js/internals/environment-v8-version.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/environment-v8-version.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var userAgent = __webpack_require__(/*! ../internals/environment-user-agent */ "./node_modules/core-js/internals/environment-user-agent.js");

var process = globalThis.process;
var Deno = globalThis.Deno;
var versions = process && process.versions || Deno && Deno.version;
var v8 = versions && versions.v8;
var match, version;

if (v8) {
  match = v8.split('.');
  // in old Chrome, versions of V8 isn't V8 = Chrome / 10
  // but their correct versions are not interesting for us
  version = match[0] > 0 && match[0] < 4 ? 1 : +(match[0] + match[1]);
}

// BrowserFS NodeJS `process` polyfill incorrectly set `.v8` to `0.0`
// so check `userAgent` even if `.v8` exists, but 0
if (!version && userAgent) {
  match = userAgent.match(/Edge\/(\d+)/);
  if (!match || match[1] >= 74) {
    match = userAgent.match(/Chrome\/(\d+)/);
    if (match) version = +match[1];
  }
}

module.exports = version;


/***/ }),

/***/ "./node_modules/core-js/internals/export.js":
/*!**************************************************!*\
  !*** ./node_modules/core-js/internals/export.js ***!
  \**************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var getOwnPropertyDescriptor = (__webpack_require__(/*! ../internals/object-get-own-property-descriptor */ "./node_modules/core-js/internals/object-get-own-property-descriptor.js").f);
var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "./node_modules/core-js/internals/create-non-enumerable-property.js");
var defineBuiltIn = __webpack_require__(/*! ../internals/define-built-in */ "./node_modules/core-js/internals/define-built-in.js");
var defineGlobalProperty = __webpack_require__(/*! ../internals/define-global-property */ "./node_modules/core-js/internals/define-global-property.js");
var copyConstructorProperties = __webpack_require__(/*! ../internals/copy-constructor-properties */ "./node_modules/core-js/internals/copy-constructor-properties.js");
var isForced = __webpack_require__(/*! ../internals/is-forced */ "./node_modules/core-js/internals/is-forced.js");

/*
  options.target         - name of the target object
  options.global         - target is the global object
  options.stat           - export as static methods of target
  options.proto          - export as prototype methods of target
  options.real           - real prototype method for the `pure` version
  options.forced         - export even if the native feature is available
  options.bind           - bind methods to the target, required for the `pure` version
  options.wrap           - wrap constructors to preventing global pollution, required for the `pure` version
  options.unsafe         - use the simple assignment of property instead of delete + defineProperty
  options.sham           - add a flag to not completely full polyfills
  options.enumerable     - export as enumerable property
  options.dontCallGetSet - prevent calling a getter on target
  options.name           - the .name of the function if it does not match the key
*/
module.exports = function (options, source) {
  var TARGET = options.target;
  var GLOBAL = options.global;
  var STATIC = options.stat;
  var FORCED, target, key, targetProperty, sourceProperty, descriptor;
  if (GLOBAL) {
    target = globalThis;
  } else if (STATIC) {
    target = globalThis[TARGET] || defineGlobalProperty(TARGET, {});
  } else {
    target = globalThis[TARGET] && globalThis[TARGET].prototype;
  }
  if (target) for (key in source) {
    sourceProperty = source[key];
    if (options.dontCallGetSet) {
      descriptor = getOwnPropertyDescriptor(target, key);
      targetProperty = descriptor && descriptor.value;
    } else targetProperty = target[key];
    FORCED = isForced(GLOBAL ? key : TARGET + (STATIC ? '.' : '#') + key, options.forced);
    // contained in target
    if (!FORCED && targetProperty !== undefined) {
      if (typeof sourceProperty == typeof targetProperty) continue;
      copyConstructorProperties(sourceProperty, targetProperty);
    }
    // add a flag to not completely full polyfills
    if (options.sham || (targetProperty && targetProperty.sham)) {
      createNonEnumerableProperty(sourceProperty, 'sham', true);
    }
    defineBuiltIn(target, key, sourceProperty, options);
  }
};


/***/ }),

/***/ "./node_modules/core-js/internals/fails.js":
/*!*************************************************!*\
  !*** ./node_modules/core-js/internals/fails.js ***!
  \*************************************************/
/***/ ((module) => {

"use strict";

module.exports = function (exec) {
  try {
    return !!exec();
  } catch (error) {
    return true;
  }
};


/***/ }),

/***/ "./node_modules/core-js/internals/fix-regexp-well-known-symbol-logic.js":
/*!******************************************************************************!*\
  !*** ./node_modules/core-js/internals/fix-regexp-well-known-symbol-logic.js ***!
  \******************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

// TODO: Remove from `core-js@4` since it's moved to entry points
__webpack_require__(/*! ../modules/es.regexp.exec */ "./node_modules/core-js/modules/es.regexp.exec.js");
var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var defineBuiltIn = __webpack_require__(/*! ../internals/define-built-in */ "./node_modules/core-js/internals/define-built-in.js");
var regexpExec = __webpack_require__(/*! ../internals/regexp-exec */ "./node_modules/core-js/internals/regexp-exec.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");
var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "./node_modules/core-js/internals/create-non-enumerable-property.js");

var SPECIES = wellKnownSymbol('species');
var RegExpPrototype = RegExp.prototype;

module.exports = function (KEY, exec, FORCED, SHAM) {
  var SYMBOL = wellKnownSymbol(KEY);

  var DELEGATES_TO_SYMBOL = !fails(function () {
    // String methods call symbol-named RegExp methods
    var O = {};
    // eslint-disable-next-line unicorn/no-immediate-mutation -- ES3 syntax limitation
    O[SYMBOL] = function () { return 7; };
    return ''[KEY](O) !== 7;
  });

  var DELEGATES_TO_EXEC = DELEGATES_TO_SYMBOL && !fails(function () {
    // Symbol-named RegExp methods call .exec
    var execCalled = false;
    var re = /a/;

    if (KEY === 'split') {
      // We can't use real regex here since it causes deoptimization
      // and serious performance degradation in V8
      // https://github.com/zloirock/core-js/issues/306
      // RegExp[@@split] doesn't call the regex's exec method, but first creates
      // a new one. We need to return the patched regex when creating the new one.
      var constructor = {};
      // eslint-disable-next-line unicorn/no-immediate-mutation -- ES3 syntax limitation
      constructor[SPECIES] = function () { return re; };
      re = { constructor: constructor, flags: '' };
      // eslint-disable-next-line unicorn/no-immediate-mutation -- ES3 syntax limitation
      re[SYMBOL] = /./[SYMBOL];
    }

    re.exec = function () {
      execCalled = true;
      return null;
    };

    re[SYMBOL]('');
    return !execCalled;
  });

  if (
    !DELEGATES_TO_SYMBOL ||
    !DELEGATES_TO_EXEC ||
    FORCED
  ) {
    var nativeRegExpMethod = /./[SYMBOL];
    var methods = exec(SYMBOL, ''[KEY], function (nativeMethod, regexp, str, arg2, forceStringMethod) {
      var $exec = regexp.exec;
      if ($exec === regexpExec || $exec === RegExpPrototype.exec) {
        if (DELEGATES_TO_SYMBOL && !forceStringMethod) {
          // The native String method already delegates to @@method (this
          // polyfilled function), leasing to infinite recursion.
          // We avoid it by directly calling the native @@method method.
          return { done: true, value: call(nativeRegExpMethod, regexp, str, arg2) };
        }
        return { done: true, value: call(nativeMethod, str, regexp, arg2) };
      }
      return { done: false };
    });

    defineBuiltIn(String.prototype, KEY, methods[0]);
    defineBuiltIn(RegExpPrototype, SYMBOL, methods[1]);
  }

  if (SHAM) createNonEnumerableProperty(RegExpPrototype[SYMBOL], 'sham', true);
};


/***/ }),

/***/ "./node_modules/core-js/internals/function-apply.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/function-apply.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var NATIVE_BIND = __webpack_require__(/*! ../internals/function-bind-native */ "./node_modules/core-js/internals/function-bind-native.js");

var FunctionPrototype = Function.prototype;
var apply = FunctionPrototype.apply;
var call = FunctionPrototype.call;

// eslint-disable-next-line es/no-function-prototype-bind, es/no-reflect -- safe
module.exports = typeof Reflect == 'object' && Reflect.apply || (NATIVE_BIND ? call.bind(apply) : function () {
  return call.apply(apply, arguments);
});


/***/ }),

/***/ "./node_modules/core-js/internals/function-bind-native.js":
/*!****************************************************************!*\
  !*** ./node_modules/core-js/internals/function-bind-native.js ***!
  \****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");

module.exports = !fails(function () {
  // eslint-disable-next-line es/no-function-prototype-bind -- safe
  var test = (function () { /* empty */ }).bind();
  // eslint-disable-next-line no-prototype-builtins -- safe
  return typeof test != 'function' || test.hasOwnProperty('prototype');
});


/***/ }),

/***/ "./node_modules/core-js/internals/function-call.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/function-call.js ***!
  \*********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var NATIVE_BIND = __webpack_require__(/*! ../internals/function-bind-native */ "./node_modules/core-js/internals/function-bind-native.js");

var call = Function.prototype.call;
// eslint-disable-next-line es/no-function-prototype-bind -- safe
module.exports = NATIVE_BIND ? call.bind(call) : function () {
  return call.apply(call, arguments);
};


/***/ }),

/***/ "./node_modules/core-js/internals/function-name.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/function-name.js ***!
  \*********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");

var FunctionPrototype = Function.prototype;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var getDescriptor = DESCRIPTORS && Object.getOwnPropertyDescriptor;

var EXISTS = hasOwn(FunctionPrototype, 'name');
// additional protection from minified / mangled / dropped function names
var PROPER = EXISTS && (function something() { /* empty */ }).name === 'something';
var CONFIGURABLE = EXISTS && (!DESCRIPTORS || (DESCRIPTORS && getDescriptor(FunctionPrototype, 'name').configurable));

module.exports = {
  EXISTS: EXISTS,
  PROPER: PROPER,
  CONFIGURABLE: CONFIGURABLE
};


/***/ }),

/***/ "./node_modules/core-js/internals/function-uncurry-this-accessor.js":
/*!**************************************************************************!*\
  !*** ./node_modules/core-js/internals/function-uncurry-this-accessor.js ***!
  \**************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var aCallable = __webpack_require__(/*! ../internals/a-callable */ "./node_modules/core-js/internals/a-callable.js");

module.exports = function (object, key, method) {
  try {
    // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
    return uncurryThis(aCallable(Object.getOwnPropertyDescriptor(object, key)[method]));
  } catch (error) { /* empty */ }
};


/***/ }),

/***/ "./node_modules/core-js/internals/function-uncurry-this.js":
/*!*****************************************************************!*\
  !*** ./node_modules/core-js/internals/function-uncurry-this.js ***!
  \*****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var NATIVE_BIND = __webpack_require__(/*! ../internals/function-bind-native */ "./node_modules/core-js/internals/function-bind-native.js");

var FunctionPrototype = Function.prototype;
var call = FunctionPrototype.call;
// eslint-disable-next-line es/no-function-prototype-bind -- safe
var uncurryThisWithBind = NATIVE_BIND && FunctionPrototype.bind.bind(call, call);

module.exports = NATIVE_BIND ? uncurryThisWithBind : function (fn) {
  return function () {
    return call.apply(fn, arguments);
  };
};


/***/ }),

/***/ "./node_modules/core-js/internals/get-built-in.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/internals/get-built-in.js ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");

var aFunction = function (argument) {
  return isCallable(argument) ? argument : undefined;
};

module.exports = function (namespace, method) {
  return arguments.length < 2 ? aFunction(globalThis[namespace]) : globalThis[namespace] && globalThis[namespace][method];
};


/***/ }),

/***/ "./node_modules/core-js/internals/get-method.js":
/*!******************************************************!*\
  !*** ./node_modules/core-js/internals/get-method.js ***!
  \******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var aCallable = __webpack_require__(/*! ../internals/a-callable */ "./node_modules/core-js/internals/a-callable.js");
var isNullOrUndefined = __webpack_require__(/*! ../internals/is-null-or-undefined */ "./node_modules/core-js/internals/is-null-or-undefined.js");

// `GetMethod` abstract operation
// https://tc39.es/ecma262/#sec-getmethod
module.exports = function (V, P) {
  var func = V[P];
  return isNullOrUndefined(func) ? undefined : aCallable(func);
};


/***/ }),

/***/ "./node_modules/core-js/internals/get-substitution.js":
/*!************************************************************!*\
  !*** ./node_modules/core-js/internals/get-substitution.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var toObject = __webpack_require__(/*! ../internals/to-object */ "./node_modules/core-js/internals/to-object.js");

var floor = Math.floor;
var charAt = uncurryThis(''.charAt);
var replace = uncurryThis(''.replace);
var stringSlice = uncurryThis(''.slice);
// eslint-disable-next-line redos/no-vulnerable -- safe
var SUBSTITUTION_SYMBOLS = /\$([$&'`]|\d{1,2}|<[^>]*>)/g;
var SUBSTITUTION_SYMBOLS_NO_NAMED = /\$([$&'`]|\d{1,2})/g;

// `GetSubstitution` abstract operation
// https://tc39.es/ecma262/#sec-getsubstitution
module.exports = function (matched, str, position, captures, namedCaptures, replacement) {
  var tailPos = position + matched.length;
  var m = captures.length;
  var symbols = SUBSTITUTION_SYMBOLS_NO_NAMED;
  if (namedCaptures !== undefined) {
    namedCaptures = toObject(namedCaptures);
    symbols = SUBSTITUTION_SYMBOLS;
  }
  return replace(replacement, symbols, function (match, ch) {
    var capture;
    switch (charAt(ch, 0)) {
      case '$': return '$';
      case '&': return matched;
      case '`': return stringSlice(str, 0, position);
      case "'": return stringSlice(str, tailPos);
      case '<':
        capture = namedCaptures[stringSlice(ch, 1, -1)];
        break;
      default: // \d\d?
        var n = +ch;
        if (n === 0) return match;
        if (n > m) {
          var f = floor(n / 10);
          if (f === 0) return match;
          if (f <= m) return captures[f - 1] === undefined ? charAt(ch, 1) : captures[f - 1] + charAt(ch, 1);
          return match;
        }
        capture = captures[n - 1];
    }
    return capture === undefined ? '' : capture;
  });
};


/***/ }),

/***/ "./node_modules/core-js/internals/global-this.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/global-this.js ***!
  \*******************************************************/
/***/ (function(module, __unused_webpack_exports, __webpack_require__) {

"use strict";

var check = function (it) {
  return it && it.Math === Math && it;
};

// https://github.com/zloirock/core-js/issues/86#issuecomment-115759028
module.exports =
  // eslint-disable-next-line es/no-global-this -- safe
  check(typeof globalThis == 'object' && globalThis) ||
  check(typeof window == 'object' && window) ||
  // eslint-disable-next-line no-restricted-globals -- safe
  check(typeof self == 'object' && self) ||
  check(typeof __webpack_require__.g == 'object' && __webpack_require__.g) ||
  check(typeof this == 'object' && this) ||
  // eslint-disable-next-line no-new-func -- fallback
  (function () { return this; })() || Function('return this')();


/***/ }),

/***/ "./node_modules/core-js/internals/has-own-property.js":
/*!************************************************************!*\
  !*** ./node_modules/core-js/internals/has-own-property.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var toObject = __webpack_require__(/*! ../internals/to-object */ "./node_modules/core-js/internals/to-object.js");

var hasOwnProperty = uncurryThis({}.hasOwnProperty);

// `HasOwnProperty` abstract operation
// https://tc39.es/ecma262/#sec-hasownproperty
// eslint-disable-next-line es/no-object-hasown -- safe
module.exports = Object.hasOwn || function hasOwn(it, key) {
  return hasOwnProperty(toObject(it), key);
};


/***/ }),

/***/ "./node_modules/core-js/internals/hidden-keys.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/hidden-keys.js ***!
  \*******************************************************/
/***/ ((module) => {

"use strict";

module.exports = {};


/***/ }),

/***/ "./node_modules/core-js/internals/html.js":
/*!************************************************!*\
  !*** ./node_modules/core-js/internals/html.js ***!
  \************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "./node_modules/core-js/internals/get-built-in.js");

module.exports = getBuiltIn('document', 'documentElement');


/***/ }),

/***/ "./node_modules/core-js/internals/ie8-dom-define.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/ie8-dom-define.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var createElement = __webpack_require__(/*! ../internals/document-create-element */ "./node_modules/core-js/internals/document-create-element.js");

// Thanks to IE8 for its funny defineProperty
module.exports = !DESCRIPTORS && !fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  return Object.defineProperty(createElement('div'), 'a', {
    get: function () { return 7; }
  }).a !== 7;
});


/***/ }),

/***/ "./node_modules/core-js/internals/indexed-object.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/indexed-object.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var classof = __webpack_require__(/*! ../internals/classof-raw */ "./node_modules/core-js/internals/classof-raw.js");

var $Object = Object;
var split = uncurryThis(''.split);

// fallback for non-array-like ES3 and non-enumerable old V8 strings
module.exports = fails(function () {
  // throws an error in rhino, see https://github.com/mozilla/rhino/issues/346
  // eslint-disable-next-line no-prototype-builtins -- safe
  return !$Object('z').propertyIsEnumerable(0);
}) ? function (it) {
  return classof(it) === 'String' ? split(it, '') : $Object(it);
} : $Object;


/***/ }),

/***/ "./node_modules/core-js/internals/inherit-if-required.js":
/*!***************************************************************!*\
  !*** ./node_modules/core-js/internals/inherit-if-required.js ***!
  \***************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var setPrototypeOf = __webpack_require__(/*! ../internals/object-set-prototype-of */ "./node_modules/core-js/internals/object-set-prototype-of.js");

// makes subclassing work correct for wrapped built-ins
module.exports = function ($this, dummy, Wrapper) {
  var NewTarget, NewTargetPrototype;
  if (
    // it can work only with native `setPrototypeOf`
    setPrototypeOf &&
    // we haven't completely correct pre-ES6 way for getting `new.target`, so use this
    isCallable(NewTarget = dummy.constructor) &&
    NewTarget !== Wrapper &&
    isObject(NewTargetPrototype = NewTarget.prototype) &&
    NewTargetPrototype !== Wrapper.prototype
  ) setPrototypeOf($this, NewTargetPrototype);
  return $this;
};


/***/ }),

/***/ "./node_modules/core-js/internals/inspect-source.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/inspect-source.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var store = __webpack_require__(/*! ../internals/shared-store */ "./node_modules/core-js/internals/shared-store.js");

var functionToString = uncurryThis(Function.toString);

// this helper broken in `core-js@3.4.1-3.4.4`, so we can't use `shared` helper
if (!isCallable(store.inspectSource)) {
  store.inspectSource = function (it) {
    return functionToString(it);
  };
}

module.exports = store.inspectSource;


/***/ }),

/***/ "./node_modules/core-js/internals/internal-state.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/internal-state.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var NATIVE_WEAK_MAP = __webpack_require__(/*! ../internals/weak-map-basic-detection */ "./node_modules/core-js/internals/weak-map-basic-detection.js");
var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "./node_modules/core-js/internals/create-non-enumerable-property.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var shared = __webpack_require__(/*! ../internals/shared-store */ "./node_modules/core-js/internals/shared-store.js");
var sharedKey = __webpack_require__(/*! ../internals/shared-key */ "./node_modules/core-js/internals/shared-key.js");
var hiddenKeys = __webpack_require__(/*! ../internals/hidden-keys */ "./node_modules/core-js/internals/hidden-keys.js");

var OBJECT_ALREADY_INITIALIZED = 'Object already initialized';
var TypeError = globalThis.TypeError;
var WeakMap = globalThis.WeakMap;
var set, get, has;

var enforce = function (it) {
  return has(it) ? get(it) : set(it, {});
};

var getterFor = function (TYPE) {
  return function (it) {
    var state;
    if (!isObject(it) || (state = get(it)).type !== TYPE) {
      throw new TypeError('Incompatible receiver, ' + TYPE + ' required');
    } return state;
  };
};

if (NATIVE_WEAK_MAP || shared.state) {
  var store = shared.state || (shared.state = new WeakMap());
  /* eslint-disable no-self-assign -- prototype methods protection */
  store.get = store.get;
  store.has = store.has;
  store.set = store.set;
  /* eslint-enable no-self-assign -- prototype methods protection */
  set = function (it, metadata) {
    if (store.has(it)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);
    metadata.facade = it;
    store.set(it, metadata);
    return metadata;
  };
  get = function (it) {
    return store.get(it) || {};
  };
  has = function (it) {
    return store.has(it);
  };
} else {
  var STATE = sharedKey('state');
  hiddenKeys[STATE] = true;
  set = function (it, metadata) {
    if (hasOwn(it, STATE)) throw new TypeError(OBJECT_ALREADY_INITIALIZED);
    metadata.facade = it;
    createNonEnumerableProperty(it, STATE, metadata);
    return metadata;
  };
  get = function (it) {
    return hasOwn(it, STATE) ? it[STATE] : {};
  };
  has = function (it) {
    return hasOwn(it, STATE);
  };
}

module.exports = {
  set: set,
  get: get,
  has: has,
  enforce: enforce,
  getterFor: getterFor
};


/***/ }),

/***/ "./node_modules/core-js/internals/is-callable.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/is-callable.js ***!
  \*******************************************************/
/***/ ((module) => {

"use strict";

// https://tc39.es/ecma262/#sec-IsHTMLDDA-internal-slot
var documentAll = typeof document == 'object' && document.all;

// `IsCallable` abstract operation
// https://tc39.es/ecma262/#sec-iscallable
// eslint-disable-next-line unicorn/no-typeof-undefined -- required for testing
module.exports = typeof documentAll == 'undefined' && documentAll !== undefined ? function (argument) {
  return typeof argument == 'function' || argument === documentAll;
} : function (argument) {
  return typeof argument == 'function';
};


/***/ }),

/***/ "./node_modules/core-js/internals/is-constructor.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/is-constructor.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var classof = __webpack_require__(/*! ../internals/classof */ "./node_modules/core-js/internals/classof.js");
var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "./node_modules/core-js/internals/get-built-in.js");
var inspectSource = __webpack_require__(/*! ../internals/inspect-source */ "./node_modules/core-js/internals/inspect-source.js");

var noop = function () { /* empty */ };
var construct = getBuiltIn('Reflect', 'construct');
var constructorRegExp = /^\s*(?:class|function)\b/;
var exec = uncurryThis(constructorRegExp.exec);
var INCORRECT_TO_STRING = !constructorRegExp.test(noop);

var isConstructorModern = function isConstructor(argument) {
  if (!isCallable(argument)) return false;
  try {
    construct(noop, [], argument);
    return true;
  } catch (error) {
    return false;
  }
};

var isConstructorLegacy = function isConstructor(argument) {
  if (!isCallable(argument)) return false;
  switch (classof(argument)) {
    case 'AsyncFunction':
    case 'GeneratorFunction':
    case 'AsyncGeneratorFunction': return false;
  }
  try {
    // we can't check .prototype since constructors produced by .bind haven't it
    // `Function#toString` throws on some built-it function in some legacy engines
    // (for example, `DOMQuad` and similar in FF41-)
    return INCORRECT_TO_STRING || !!exec(constructorRegExp, inspectSource(argument));
  } catch (error) {
    return true;
  }
};

isConstructorLegacy.sham = true;

// `IsConstructor` abstract operation
// https://tc39.es/ecma262/#sec-isconstructor
module.exports = !construct || fails(function () {
  var called;
  return isConstructorModern(isConstructorModern.call)
    || !isConstructorModern(Object)
    || !isConstructorModern(function () { called = true; })
    || called;
}) ? isConstructorLegacy : isConstructorModern;


/***/ }),

/***/ "./node_modules/core-js/internals/is-forced.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/is-forced.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");

var replacement = /#|\.prototype\./;

var isForced = function (feature, detection) {
  var value = data[normalize(feature)];
  return value === POLYFILL ? true
    : value === NATIVE ? false
    : isCallable(detection) ? fails(detection)
    : !!detection;
};

var normalize = isForced.normalize = function (string) {
  return String(string).replace(replacement, '.').toLowerCase();
};

var data = isForced.data = {};
var NATIVE = isForced.NATIVE = 'N';
var POLYFILL = isForced.POLYFILL = 'P';

module.exports = isForced;


/***/ }),

/***/ "./node_modules/core-js/internals/is-null-or-undefined.js":
/*!****************************************************************!*\
  !*** ./node_modules/core-js/internals/is-null-or-undefined.js ***!
  \****************************************************************/
/***/ ((module) => {

"use strict";

// we can't use just `it == null` since of `document.all` special case
// https://tc39.es/ecma262/#sec-IsHTMLDDA-internal-slot-aec
module.exports = function (it) {
  return it === null || it === undefined;
};


/***/ }),

/***/ "./node_modules/core-js/internals/is-object.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/is-object.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");

module.exports = function (it) {
  return typeof it == 'object' ? it !== null : isCallable(it);
};


/***/ }),

/***/ "./node_modules/core-js/internals/is-possible-prototype.js":
/*!*****************************************************************!*\
  !*** ./node_modules/core-js/internals/is-possible-prototype.js ***!
  \*****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");

module.exports = function (argument) {
  return isObject(argument) || argument === null;
};


/***/ }),

/***/ "./node_modules/core-js/internals/is-pure.js":
/*!***************************************************!*\
  !*** ./node_modules/core-js/internals/is-pure.js ***!
  \***************************************************/
/***/ ((module) => {

"use strict";

module.exports = false;


/***/ }),

/***/ "./node_modules/core-js/internals/is-regexp.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/is-regexp.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var classof = __webpack_require__(/*! ../internals/classof-raw */ "./node_modules/core-js/internals/classof-raw.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var MATCH = wellKnownSymbol('match');

// `IsRegExp` abstract operation
// https://tc39.es/ecma262/#sec-isregexp
module.exports = function (it) {
  var isRegExp;
  return isObject(it) && ((isRegExp = it[MATCH]) !== undefined ? !!isRegExp : classof(it) === 'RegExp');
};


/***/ }),

/***/ "./node_modules/core-js/internals/is-symbol.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/is-symbol.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "./node_modules/core-js/internals/get-built-in.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var isPrototypeOf = __webpack_require__(/*! ../internals/object-is-prototype-of */ "./node_modules/core-js/internals/object-is-prototype-of.js");
var USE_SYMBOL_AS_UID = __webpack_require__(/*! ../internals/use-symbol-as-uid */ "./node_modules/core-js/internals/use-symbol-as-uid.js");

var $Object = Object;

module.exports = USE_SYMBOL_AS_UID ? function (it) {
  return typeof it == 'symbol';
} : function (it) {
  var $Symbol = getBuiltIn('Symbol');
  return isCallable($Symbol) && isPrototypeOf($Symbol.prototype, $Object(it));
};


/***/ }),

/***/ "./node_modules/core-js/internals/iterator-create-constructor.js":
/*!***********************************************************************!*\
  !*** ./node_modules/core-js/internals/iterator-create-constructor.js ***!
  \***********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var IteratorPrototype = (__webpack_require__(/*! ../internals/iterators-core */ "./node_modules/core-js/internals/iterators-core.js").IteratorPrototype);
var create = __webpack_require__(/*! ../internals/object-create */ "./node_modules/core-js/internals/object-create.js");
var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "./node_modules/core-js/internals/create-property-descriptor.js");
var setToStringTag = __webpack_require__(/*! ../internals/set-to-string-tag */ "./node_modules/core-js/internals/set-to-string-tag.js");
var Iterators = __webpack_require__(/*! ../internals/iterators */ "./node_modules/core-js/internals/iterators.js");

var returnThis = function () { return this; };

module.exports = function (IteratorConstructor, NAME, next, ENUMERABLE_NEXT) {
  var TO_STRING_TAG = NAME + ' Iterator';
  IteratorConstructor.prototype = create(IteratorPrototype, { next: createPropertyDescriptor(+!ENUMERABLE_NEXT, next) });
  setToStringTag(IteratorConstructor, TO_STRING_TAG, false, true);
  Iterators[TO_STRING_TAG] = returnThis;
  return IteratorConstructor;
};


/***/ }),

/***/ "./node_modules/core-js/internals/iterator-define.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/internals/iterator-define.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "./node_modules/core-js/internals/is-pure.js");
var FunctionName = __webpack_require__(/*! ../internals/function-name */ "./node_modules/core-js/internals/function-name.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var createIteratorConstructor = __webpack_require__(/*! ../internals/iterator-create-constructor */ "./node_modules/core-js/internals/iterator-create-constructor.js");
var getPrototypeOf = __webpack_require__(/*! ../internals/object-get-prototype-of */ "./node_modules/core-js/internals/object-get-prototype-of.js");
var setPrototypeOf = __webpack_require__(/*! ../internals/object-set-prototype-of */ "./node_modules/core-js/internals/object-set-prototype-of.js");
var setToStringTag = __webpack_require__(/*! ../internals/set-to-string-tag */ "./node_modules/core-js/internals/set-to-string-tag.js");
var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "./node_modules/core-js/internals/create-non-enumerable-property.js");
var defineBuiltIn = __webpack_require__(/*! ../internals/define-built-in */ "./node_modules/core-js/internals/define-built-in.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");
var Iterators = __webpack_require__(/*! ../internals/iterators */ "./node_modules/core-js/internals/iterators.js");
var IteratorsCore = __webpack_require__(/*! ../internals/iterators-core */ "./node_modules/core-js/internals/iterators-core.js");

var PROPER_FUNCTION_NAME = FunctionName.PROPER;
var CONFIGURABLE_FUNCTION_NAME = FunctionName.CONFIGURABLE;
var IteratorPrototype = IteratorsCore.IteratorPrototype;
var BUGGY_SAFARI_ITERATORS = IteratorsCore.BUGGY_SAFARI_ITERATORS;
var ITERATOR = wellKnownSymbol('iterator');
var KEYS = 'keys';
var VALUES = 'values';
var ENTRIES = 'entries';

var returnThis = function () { return this; };

module.exports = function (Iterable, NAME, IteratorConstructor, next, DEFAULT, IS_SET, FORCED) {
  createIteratorConstructor(IteratorConstructor, NAME, next);

  var getIterationMethod = function (KIND) {
    if (KIND === DEFAULT && defaultIterator) return defaultIterator;
    if (!BUGGY_SAFARI_ITERATORS && KIND && KIND in IterablePrototype) return IterablePrototype[KIND];

    switch (KIND) {
      case KEYS: return function keys() { return new IteratorConstructor(this, KIND); };
      case VALUES: return function values() { return new IteratorConstructor(this, KIND); };
      case ENTRIES: return function entries() { return new IteratorConstructor(this, KIND); };
    }

    return function () { return new IteratorConstructor(this); };
  };

  var TO_STRING_TAG = NAME + ' Iterator';
  var INCORRECT_VALUES_NAME = false;
  var IterablePrototype = Iterable.prototype;
  var nativeIterator = IterablePrototype[ITERATOR]
    || IterablePrototype['@@iterator']
    || DEFAULT && IterablePrototype[DEFAULT];
  var defaultIterator = !BUGGY_SAFARI_ITERATORS && nativeIterator || getIterationMethod(DEFAULT);
  var anyNativeIterator = NAME === 'Array' ? IterablePrototype.entries || nativeIterator : nativeIterator;
  var CurrentIteratorPrototype, methods, KEY;

  // fix native
  if (anyNativeIterator) {
    CurrentIteratorPrototype = getPrototypeOf(anyNativeIterator.call(new Iterable()));
    if (CurrentIteratorPrototype !== Object.prototype && CurrentIteratorPrototype.next) {
      if (!IS_PURE && getPrototypeOf(CurrentIteratorPrototype) !== IteratorPrototype) {
        if (setPrototypeOf) {
          setPrototypeOf(CurrentIteratorPrototype, IteratorPrototype);
        } else if (!isCallable(CurrentIteratorPrototype[ITERATOR])) {
          defineBuiltIn(CurrentIteratorPrototype, ITERATOR, returnThis);
        }
      }
      // Set @@toStringTag to native iterators
      setToStringTag(CurrentIteratorPrototype, TO_STRING_TAG, true, true);
      if (IS_PURE) Iterators[TO_STRING_TAG] = returnThis;
    }
  }

  // fix Array.prototype.{ values, @@iterator }.name in V8 / FF
  if (PROPER_FUNCTION_NAME && DEFAULT === VALUES && nativeIterator && nativeIterator.name !== VALUES) {
    if (!IS_PURE && CONFIGURABLE_FUNCTION_NAME) {
      createNonEnumerableProperty(IterablePrototype, 'name', VALUES);
    } else {
      INCORRECT_VALUES_NAME = true;
      defaultIterator = function values() { return call(nativeIterator, this); };
    }
  }

  // export additional methods
  if (DEFAULT) {
    methods = {
      values: getIterationMethod(VALUES),
      keys: IS_SET ? defaultIterator : getIterationMethod(KEYS),
      entries: getIterationMethod(ENTRIES)
    };
    if (FORCED) for (KEY in methods) {
      if (BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME || !(KEY in IterablePrototype)) {
        defineBuiltIn(IterablePrototype, KEY, methods[KEY]);
      }
    } else $({ target: NAME, proto: true, forced: BUGGY_SAFARI_ITERATORS || INCORRECT_VALUES_NAME }, methods);
  }

  // define iterator
  if ((!IS_PURE || FORCED) && IterablePrototype[ITERATOR] !== defaultIterator) {
    defineBuiltIn(IterablePrototype, ITERATOR, defaultIterator, { name: DEFAULT });
  }
  Iterators[NAME] = defaultIterator;

  return methods;
};


/***/ }),

/***/ "./node_modules/core-js/internals/iterators-core.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/iterators-core.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var create = __webpack_require__(/*! ../internals/object-create */ "./node_modules/core-js/internals/object-create.js");
var getPrototypeOf = __webpack_require__(/*! ../internals/object-get-prototype-of */ "./node_modules/core-js/internals/object-get-prototype-of.js");
var defineBuiltIn = __webpack_require__(/*! ../internals/define-built-in */ "./node_modules/core-js/internals/define-built-in.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");
var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "./node_modules/core-js/internals/is-pure.js");

var ITERATOR = wellKnownSymbol('iterator');
var BUGGY_SAFARI_ITERATORS = false;

// `%IteratorPrototype%` object
// https://tc39.es/ecma262/#sec-%iteratorprototype%-object
var IteratorPrototype, PrototypeOfArrayIteratorPrototype, arrayIterator;

/* eslint-disable es/no-array-prototype-keys -- safe */
if ([].keys) {
  arrayIterator = [].keys();
  // Safari 8 has buggy iterators w/o `next`
  if (!('next' in arrayIterator)) BUGGY_SAFARI_ITERATORS = true;
  else {
    PrototypeOfArrayIteratorPrototype = getPrototypeOf(getPrototypeOf(arrayIterator));
    if (PrototypeOfArrayIteratorPrototype !== Object.prototype) IteratorPrototype = PrototypeOfArrayIteratorPrototype;
  }
}

var NEW_ITERATOR_PROTOTYPE = !isObject(IteratorPrototype) || fails(function () {
  var test = {};
  // FF44- legacy iterators case
  return IteratorPrototype[ITERATOR].call(test) !== test;
});

if (NEW_ITERATOR_PROTOTYPE) IteratorPrototype = {};
else if (IS_PURE) IteratorPrototype = create(IteratorPrototype);

// `%IteratorPrototype%[@@iterator]()` method
// https://tc39.es/ecma262/#sec-%iteratorprototype%-@@iterator
if (!isCallable(IteratorPrototype[ITERATOR])) {
  defineBuiltIn(IteratorPrototype, ITERATOR, function () {
    return this;
  });
}

module.exports = {
  IteratorPrototype: IteratorPrototype,
  BUGGY_SAFARI_ITERATORS: BUGGY_SAFARI_ITERATORS
};


/***/ }),

/***/ "./node_modules/core-js/internals/iterators.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/iterators.js ***!
  \*****************************************************/
/***/ ((module) => {

"use strict";

module.exports = {};


/***/ }),

/***/ "./node_modules/core-js/internals/length-of-array-like.js":
/*!****************************************************************!*\
  !*** ./node_modules/core-js/internals/length-of-array-like.js ***!
  \****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var toLength = __webpack_require__(/*! ../internals/to-length */ "./node_modules/core-js/internals/to-length.js");

// `LengthOfArrayLike` abstract operation
// https://tc39.es/ecma262/#sec-lengthofarraylike
module.exports = function (obj) {
  return toLength(obj.length);
};


/***/ }),

/***/ "./node_modules/core-js/internals/make-built-in.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/make-built-in.js ***!
  \*********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var CONFIGURABLE_FUNCTION_NAME = (__webpack_require__(/*! ../internals/function-name */ "./node_modules/core-js/internals/function-name.js").CONFIGURABLE);
var inspectSource = __webpack_require__(/*! ../internals/inspect-source */ "./node_modules/core-js/internals/inspect-source.js");
var InternalStateModule = __webpack_require__(/*! ../internals/internal-state */ "./node_modules/core-js/internals/internal-state.js");

var enforceInternalState = InternalStateModule.enforce;
var getInternalState = InternalStateModule.get;
var $String = String;
// eslint-disable-next-line es/no-object-defineproperty -- safe
var defineProperty = Object.defineProperty;
var stringSlice = uncurryThis(''.slice);
var replace = uncurryThis(''.replace);
var join = uncurryThis([].join);

var CONFIGURABLE_LENGTH = DESCRIPTORS && !fails(function () {
  return defineProperty(function () { /* empty */ }, 'length', { value: 8 }).length !== 8;
});

var TEMPLATE = String(String).split('String');

var makeBuiltIn = module.exports = function (value, name, options) {
  if (stringSlice($String(name), 0, 7) === 'Symbol(') {
    name = '[' + replace($String(name), /^Symbol\(([^)]*)\).*$/, '$1') + ']';
  }
  if (options && options.getter) name = 'get ' + name;
  if (options && options.setter) name = 'set ' + name;
  if (!hasOwn(value, 'name') || (CONFIGURABLE_FUNCTION_NAME && value.name !== name)) {
    if (DESCRIPTORS) defineProperty(value, 'name', { value: name, configurable: true });
    else value.name = name;
  }
  if (CONFIGURABLE_LENGTH && options && hasOwn(options, 'arity') && value.length !== options.arity) {
    defineProperty(value, 'length', { value: options.arity });
  }
  try {
    if (options && hasOwn(options, 'constructor') && options.constructor) {
      if (DESCRIPTORS) defineProperty(value, 'prototype', { writable: false });
    // in V8 ~ Chrome 53, prototypes of some methods, like `Array.prototype.values`, are non-writable
    } else if (value.prototype) value.prototype = undefined;
  } catch (error) { /* empty */ }
  var state = enforceInternalState(value);
  if (!hasOwn(state, 'source')) {
    state.source = join(TEMPLATE, typeof name == 'string' ? name : '');
  } return value;
};

// add fake Function#toString for correct work wrapped methods / constructors with methods like LoDash isNative
// eslint-disable-next-line no-extend-native -- required
Function.prototype.toString = makeBuiltIn(function toString() {
  return isCallable(this) && getInternalState(this).source || inspectSource(this);
}, 'toString');


/***/ }),

/***/ "./node_modules/core-js/internals/math-trunc.js":
/*!******************************************************!*\
  !*** ./node_modules/core-js/internals/math-trunc.js ***!
  \******************************************************/
/***/ ((module) => {

"use strict";

var ceil = Math.ceil;
var floor = Math.floor;

// `Math.trunc` method
// https://tc39.es/ecma262/#sec-math.trunc
// eslint-disable-next-line es/no-math-trunc -- safe
module.exports = Math.trunc || function trunc(x) {
  var n = +x;
  return (n > 0 ? floor : ceil)(n);
};


/***/ }),

/***/ "./node_modules/core-js/internals/new-promise-capability.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/new-promise-capability.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var aCallable = __webpack_require__(/*! ../internals/a-callable */ "./node_modules/core-js/internals/a-callable.js");

var $TypeError = TypeError;

var PromiseCapability = function (C) {
  var resolve, reject;
  this.promise = new C(function ($$resolve, $$reject) {
    if (resolve !== undefined || reject !== undefined) throw new $TypeError('Bad Promise constructor');
    resolve = $$resolve;
    reject = $$reject;
  });
  this.resolve = aCallable(resolve);
  this.reject = aCallable(reject);
};

// `NewPromiseCapability` abstract operation
// https://tc39.es/ecma262/#sec-newpromisecapability
module.exports.f = function (C) {
  return new PromiseCapability(C);
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-create.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/object-create.js ***!
  \*********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

/* global ActiveXObject -- old IE, WSH */
var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");
var definePropertiesModule = __webpack_require__(/*! ../internals/object-define-properties */ "./node_modules/core-js/internals/object-define-properties.js");
var enumBugKeys = __webpack_require__(/*! ../internals/enum-bug-keys */ "./node_modules/core-js/internals/enum-bug-keys.js");
var hiddenKeys = __webpack_require__(/*! ../internals/hidden-keys */ "./node_modules/core-js/internals/hidden-keys.js");
var html = __webpack_require__(/*! ../internals/html */ "./node_modules/core-js/internals/html.js");
var documentCreateElement = __webpack_require__(/*! ../internals/document-create-element */ "./node_modules/core-js/internals/document-create-element.js");
var sharedKey = __webpack_require__(/*! ../internals/shared-key */ "./node_modules/core-js/internals/shared-key.js");

var GT = '>';
var LT = '<';
var PROTOTYPE = 'prototype';
var SCRIPT = 'script';
var IE_PROTO = sharedKey('IE_PROTO');

var EmptyConstructor = function () { /* empty */ };

var scriptTag = function (content) {
  return LT + SCRIPT + GT + content + LT + '/' + SCRIPT + GT;
};

// Create object with fake `null` prototype: use ActiveX Object with cleared prototype
var NullProtoObjectViaActiveX = function (activeXDocument) {
  activeXDocument.write(scriptTag(''));
  activeXDocument.close();
  var temp = activeXDocument.parentWindow.Object;
  // eslint-disable-next-line no-useless-assignment -- avoid memory leak
  activeXDocument = null;
  return temp;
};

// Create object with fake `null` prototype: use iframe Object with cleared prototype
var NullProtoObjectViaIFrame = function () {
  // Thrash, waste and sodomy: IE GC bug
  var iframe = documentCreateElement('iframe');
  var JS = 'java' + SCRIPT + ':';
  var iframeDocument;
  iframe.style.display = 'none';
  html.appendChild(iframe);
  // https://github.com/zloirock/core-js/issues/475
  iframe.src = String(JS);
  iframeDocument = iframe.contentWindow.document;
  iframeDocument.open();
  iframeDocument.write(scriptTag('document.F=Object'));
  iframeDocument.close();
  return iframeDocument.F;
};

// Check for document.domain and active x support
// No need to use active x approach when document.domain is not set
// see https://github.com/es-shims/es5-shim/issues/150
// variation of https://github.com/kitcambridge/es5-shim/commit/4f738ac066346
// avoid IE GC bug
var activeXDocument;
var NullProtoObject = function () {
  try {
    activeXDocument = new ActiveXObject('htmlfile');
  } catch (error) { /* ignore */ }
  NullProtoObject = typeof document != 'undefined'
    ? document.domain && activeXDocument
      ? NullProtoObjectViaActiveX(activeXDocument) // old IE
      : NullProtoObjectViaIFrame()
    : NullProtoObjectViaActiveX(activeXDocument); // WSH
  var length = enumBugKeys.length;
  while (length--) delete NullProtoObject[PROTOTYPE][enumBugKeys[length]];
  return NullProtoObject();
};

hiddenKeys[IE_PROTO] = true;

// `Object.create` method
// https://tc39.es/ecma262/#sec-object.create
// eslint-disable-next-line es/no-object-create -- safe
module.exports = Object.create || function create(O, Properties) {
  var result;
  if (O !== null) {
    EmptyConstructor[PROTOTYPE] = anObject(O);
    result = new EmptyConstructor();
    EmptyConstructor[PROTOTYPE] = null;
    // add "__proto__" for Object.getPrototypeOf polyfill
    result[IE_PROTO] = O;
  } else result = NullProtoObject();
  return Properties === undefined ? result : definePropertiesModule.f(result, Properties);
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-define-properties.js":
/*!********************************************************************!*\
  !*** ./node_modules/core-js/internals/object-define-properties.js ***!
  \********************************************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var V8_PROTOTYPE_DEFINE_BUG = __webpack_require__(/*! ../internals/v8-prototype-define-bug */ "./node_modules/core-js/internals/v8-prototype-define-bug.js");
var definePropertyModule = __webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js");
var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");
var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "./node_modules/core-js/internals/to-indexed-object.js");
var objectKeys = __webpack_require__(/*! ../internals/object-keys */ "./node_modules/core-js/internals/object-keys.js");

// `Object.defineProperties` method
// https://tc39.es/ecma262/#sec-object.defineproperties
// eslint-disable-next-line es/no-object-defineproperties -- safe
exports.f = DESCRIPTORS && !V8_PROTOTYPE_DEFINE_BUG ? Object.defineProperties : function defineProperties(O, Properties) {
  anObject(O);
  var props = toIndexedObject(Properties);
  var keys = objectKeys(Properties);
  var length = keys.length;
  var index = 0;
  var key;
  while (length > index) definePropertyModule.f(O, key = keys[index++], props[key]);
  return O;
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-define-property.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/object-define-property.js ***!
  \******************************************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var IE8_DOM_DEFINE = __webpack_require__(/*! ../internals/ie8-dom-define */ "./node_modules/core-js/internals/ie8-dom-define.js");
var V8_PROTOTYPE_DEFINE_BUG = __webpack_require__(/*! ../internals/v8-prototype-define-bug */ "./node_modules/core-js/internals/v8-prototype-define-bug.js");
var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");
var toPropertyKey = __webpack_require__(/*! ../internals/to-property-key */ "./node_modules/core-js/internals/to-property-key.js");

var $TypeError = TypeError;
// eslint-disable-next-line es/no-object-defineproperty -- safe
var $defineProperty = Object.defineProperty;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;
var ENUMERABLE = 'enumerable';
var CONFIGURABLE = 'configurable';
var WRITABLE = 'writable';

// `Object.defineProperty` method
// https://tc39.es/ecma262/#sec-object.defineproperty
exports.f = DESCRIPTORS ? V8_PROTOTYPE_DEFINE_BUG ? function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPropertyKey(P);
  anObject(Attributes);
  if (typeof O === 'function' && P === 'prototype' && 'value' in Attributes && WRITABLE in Attributes && !Attributes[WRITABLE]) {
    var current = $getOwnPropertyDescriptor(O, P);
    if (current && current[WRITABLE]) {
      O[P] = Attributes.value;
      Attributes = {
        configurable: CONFIGURABLE in Attributes ? Attributes[CONFIGURABLE] : current[CONFIGURABLE],
        enumerable: ENUMERABLE in Attributes ? Attributes[ENUMERABLE] : current[ENUMERABLE],
        writable: false
      };
    }
  } return $defineProperty(O, P, Attributes);
} : $defineProperty : function defineProperty(O, P, Attributes) {
  anObject(O);
  P = toPropertyKey(P);
  anObject(Attributes);
  if (IE8_DOM_DEFINE) try {
    return $defineProperty(O, P, Attributes);
  } catch (error) { /* empty */ }
  if ('get' in Attributes || 'set' in Attributes) throw new $TypeError('Accessors not supported');
  if ('value' in Attributes) O[P] = Attributes.value;
  return O;
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-get-own-property-descriptor.js":
/*!******************************************************************************!*\
  !*** ./node_modules/core-js/internals/object-get-own-property-descriptor.js ***!
  \******************************************************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var propertyIsEnumerableModule = __webpack_require__(/*! ../internals/object-property-is-enumerable */ "./node_modules/core-js/internals/object-property-is-enumerable.js");
var createPropertyDescriptor = __webpack_require__(/*! ../internals/create-property-descriptor */ "./node_modules/core-js/internals/create-property-descriptor.js");
var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "./node_modules/core-js/internals/to-indexed-object.js");
var toPropertyKey = __webpack_require__(/*! ../internals/to-property-key */ "./node_modules/core-js/internals/to-property-key.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var IE8_DOM_DEFINE = __webpack_require__(/*! ../internals/ie8-dom-define */ "./node_modules/core-js/internals/ie8-dom-define.js");

// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var $getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

// `Object.getOwnPropertyDescriptor` method
// https://tc39.es/ecma262/#sec-object.getownpropertydescriptor
exports.f = DESCRIPTORS ? $getOwnPropertyDescriptor : function getOwnPropertyDescriptor(O, P) {
  O = toIndexedObject(O);
  P = toPropertyKey(P);
  if (IE8_DOM_DEFINE) try {
    return $getOwnPropertyDescriptor(O, P);
  } catch (error) { /* empty */ }
  if (hasOwn(O, P)) return createPropertyDescriptor(!call(propertyIsEnumerableModule.f, O, P), O[P]);
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-get-own-property-names.js":
/*!*************************************************************************!*\
  !*** ./node_modules/core-js/internals/object-get-own-property-names.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, exports, __webpack_require__) => {

"use strict";

var internalObjectKeys = __webpack_require__(/*! ../internals/object-keys-internal */ "./node_modules/core-js/internals/object-keys-internal.js");
var enumBugKeys = __webpack_require__(/*! ../internals/enum-bug-keys */ "./node_modules/core-js/internals/enum-bug-keys.js");

var hiddenKeys = enumBugKeys.concat('length', 'prototype');

// `Object.getOwnPropertyNames` method
// https://tc39.es/ecma262/#sec-object.getownpropertynames
// eslint-disable-next-line es/no-object-getownpropertynames -- safe
exports.f = Object.getOwnPropertyNames || function getOwnPropertyNames(O) {
  return internalObjectKeys(O, hiddenKeys);
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-get-own-property-symbols.js":
/*!***************************************************************************!*\
  !*** ./node_modules/core-js/internals/object-get-own-property-symbols.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";

// eslint-disable-next-line es/no-object-getownpropertysymbols -- safe
exports.f = Object.getOwnPropertySymbols;


/***/ }),

/***/ "./node_modules/core-js/internals/object-get-prototype-of.js":
/*!*******************************************************************!*\
  !*** ./node_modules/core-js/internals/object-get-prototype-of.js ***!
  \*******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var toObject = __webpack_require__(/*! ../internals/to-object */ "./node_modules/core-js/internals/to-object.js");
var sharedKey = __webpack_require__(/*! ../internals/shared-key */ "./node_modules/core-js/internals/shared-key.js");
var CORRECT_PROTOTYPE_GETTER = __webpack_require__(/*! ../internals/correct-prototype-getter */ "./node_modules/core-js/internals/correct-prototype-getter.js");

var IE_PROTO = sharedKey('IE_PROTO');
var $Object = Object;
var ObjectPrototype = $Object.prototype;

// `Object.getPrototypeOf` method
// https://tc39.es/ecma262/#sec-object.getprototypeof
// eslint-disable-next-line es/no-object-getprototypeof -- safe
module.exports = CORRECT_PROTOTYPE_GETTER ? $Object.getPrototypeOf : function (O) {
  var object = toObject(O);
  if (hasOwn(object, IE_PROTO)) return object[IE_PROTO];
  var constructor = object.constructor;
  if (isCallable(constructor) && object instanceof constructor) {
    return constructor.prototype;
  } return object instanceof $Object ? ObjectPrototype : null;
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-is-prototype-of.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/object-is-prototype-of.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");

module.exports = uncurryThis({}.isPrototypeOf);


/***/ }),

/***/ "./node_modules/core-js/internals/object-keys-internal.js":
/*!****************************************************************!*\
  !*** ./node_modules/core-js/internals/object-keys-internal.js ***!
  \****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "./node_modules/core-js/internals/to-indexed-object.js");
var indexOf = (__webpack_require__(/*! ../internals/array-includes */ "./node_modules/core-js/internals/array-includes.js").indexOf);
var hiddenKeys = __webpack_require__(/*! ../internals/hidden-keys */ "./node_modules/core-js/internals/hidden-keys.js");

var push = uncurryThis([].push);

module.exports = function (object, names) {
  var O = toIndexedObject(object);
  var i = 0;
  var result = [];
  var key;
  for (key in O) !hasOwn(hiddenKeys, key) && hasOwn(O, key) && push(result, key);
  // Don't enum bug & hidden keys
  while (names.length > i) if (hasOwn(O, key = names[i++])) {
    ~indexOf(result, key) || push(result, key);
  }
  return result;
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-keys.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/object-keys.js ***!
  \*******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var internalObjectKeys = __webpack_require__(/*! ../internals/object-keys-internal */ "./node_modules/core-js/internals/object-keys-internal.js");
var enumBugKeys = __webpack_require__(/*! ../internals/enum-bug-keys */ "./node_modules/core-js/internals/enum-bug-keys.js");

// `Object.keys` method
// https://tc39.es/ecma262/#sec-object.keys
// eslint-disable-next-line es/no-object-keys -- safe
module.exports = Object.keys || function keys(O) {
  return internalObjectKeys(O, enumBugKeys);
};


/***/ }),

/***/ "./node_modules/core-js/internals/object-property-is-enumerable.js":
/*!*************************************************************************!*\
  !*** ./node_modules/core-js/internals/object-property-is-enumerable.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack_module, exports) => {

"use strict";

var $propertyIsEnumerable = {}.propertyIsEnumerable;
// eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
var getOwnPropertyDescriptor = Object.getOwnPropertyDescriptor;

// Nashorn ~ JDK8 bug
var NASHORN_BUG = getOwnPropertyDescriptor && !$propertyIsEnumerable.call({ 1: 2 }, 1);

// `Object.prototype.propertyIsEnumerable` method implementation
// https://tc39.es/ecma262/#sec-object.prototype.propertyisenumerable
exports.f = NASHORN_BUG ? function propertyIsEnumerable(V) {
  var descriptor = getOwnPropertyDescriptor(this, V);
  return !!descriptor && descriptor.enumerable;
} : $propertyIsEnumerable;


/***/ }),

/***/ "./node_modules/core-js/internals/object-set-prototype-of.js":
/*!*******************************************************************!*\
  !*** ./node_modules/core-js/internals/object-set-prototype-of.js ***!
  \*******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

/* eslint-disable no-proto -- safe */
var uncurryThisAccessor = __webpack_require__(/*! ../internals/function-uncurry-this-accessor */ "./node_modules/core-js/internals/function-uncurry-this-accessor.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "./node_modules/core-js/internals/require-object-coercible.js");
var aPossiblePrototype = __webpack_require__(/*! ../internals/a-possible-prototype */ "./node_modules/core-js/internals/a-possible-prototype.js");

// `Object.setPrototypeOf` method
// https://tc39.es/ecma262/#sec-object.setprototypeof
// Works with __proto__ only. Old v8 can't work with null proto objects.
// eslint-disable-next-line es/no-object-setprototypeof -- safe
module.exports = Object.setPrototypeOf || ('__proto__' in {} ? function () {
  var CORRECT_SETTER = false;
  var test = {};
  var setter;
  try {
    setter = uncurryThisAccessor(Object.prototype, '__proto__', 'set');
    setter(test, []);
    CORRECT_SETTER = test instanceof Array;
  } catch (error) { /* empty */ }
  return function setPrototypeOf(O, proto) {
    requireObjectCoercible(O);
    aPossiblePrototype(proto);
    if (!isObject(O)) return O;
    if (CORRECT_SETTER) setter(O, proto);
    else O.__proto__ = proto;
    return O;
  };
}() : undefined);


/***/ }),

/***/ "./node_modules/core-js/internals/ordinary-to-primitive.js":
/*!*****************************************************************!*\
  !*** ./node_modules/core-js/internals/ordinary-to-primitive.js ***!
  \*****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");

var $TypeError = TypeError;

// `OrdinaryToPrimitive` abstract operation
// https://tc39.es/ecma262/#sec-ordinarytoprimitive
module.exports = function (input, pref) {
  var fn, val;
  if (pref === 'string' && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;
  if (isCallable(fn = input.valueOf) && !isObject(val = call(fn, input))) return val;
  if (pref !== 'string' && isCallable(fn = input.toString) && !isObject(val = call(fn, input))) return val;
  throw new $TypeError("Can't convert object to primitive value");
};


/***/ }),

/***/ "./node_modules/core-js/internals/own-keys.js":
/*!****************************************************!*\
  !*** ./node_modules/core-js/internals/own-keys.js ***!
  \****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "./node_modules/core-js/internals/get-built-in.js");
var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var getOwnPropertyNamesModule = __webpack_require__(/*! ../internals/object-get-own-property-names */ "./node_modules/core-js/internals/object-get-own-property-names.js");
var getOwnPropertySymbolsModule = __webpack_require__(/*! ../internals/object-get-own-property-symbols */ "./node_modules/core-js/internals/object-get-own-property-symbols.js");
var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");

var concat = uncurryThis([].concat);

// all object keys, includes non-enumerable and symbols
module.exports = getBuiltIn('Reflect', 'ownKeys') || function ownKeys(it) {
  var keys = getOwnPropertyNamesModule.f(anObject(it));
  var getOwnPropertySymbols = getOwnPropertySymbolsModule.f;
  return getOwnPropertySymbols ? concat(keys, getOwnPropertySymbols(it)) : keys;
};


/***/ }),

/***/ "./node_modules/core-js/internals/promise-native-constructor.js":
/*!**********************************************************************!*\
  !*** ./node_modules/core-js/internals/promise-native-constructor.js ***!
  \**********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");

module.exports = globalThis.Promise;


/***/ }),

/***/ "./node_modules/core-js/internals/promise-resolve.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/internals/promise-resolve.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var newPromiseCapability = __webpack_require__(/*! ../internals/new-promise-capability */ "./node_modules/core-js/internals/new-promise-capability.js");

module.exports = function (C, x) {
  anObject(C);
  if (isObject(x) && x.constructor === C) return x;
  var promiseCapability = newPromiseCapability.f(C);
  var resolve = promiseCapability.resolve;
  resolve(x);
  return promiseCapability.promise;
};


/***/ }),

/***/ "./node_modules/core-js/internals/proxy-accessor.js":
/*!**********************************************************!*\
  !*** ./node_modules/core-js/internals/proxy-accessor.js ***!
  \**********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var defineProperty = (__webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js").f);

module.exports = function (Target, Source, key) {
  key in Target || defineProperty(Target, key, {
    configurable: true,
    get: function () { return Source[key]; },
    set: function (it) { Source[key] = it; }
  });
};


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-exec-abstract.js":
/*!****************************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-exec-abstract.js ***!
  \****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var classof = __webpack_require__(/*! ../internals/classof-raw */ "./node_modules/core-js/internals/classof-raw.js");
var regexpExec = __webpack_require__(/*! ../internals/regexp-exec */ "./node_modules/core-js/internals/regexp-exec.js");

var $TypeError = TypeError;

// `RegExpExec` abstract operation
// https://tc39.es/ecma262/#sec-regexpexec
module.exports = function (R, S) {
  var exec = R.exec;
  if (isCallable(exec)) {
    var result = call(exec, R, S);
    if (result !== null) anObject(result);
    return result;
  }
  if (classof(R) === 'RegExp') return call(regexpExec, R, S);
  throw new $TypeError('RegExp#exec called on incompatible receiver');
};


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-exec.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-exec.js ***!
  \*******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

/* eslint-disable regexp/no-empty-capturing-group, regexp/no-empty-group, regexp/no-lazy-ends -- testing */
/* eslint-disable regexp/no-useless-quantifier -- testing */
var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var toString = __webpack_require__(/*! ../internals/to-string */ "./node_modules/core-js/internals/to-string.js");
var regexpFlags = __webpack_require__(/*! ../internals/regexp-flags */ "./node_modules/core-js/internals/regexp-flags.js");
var stickyHelpers = __webpack_require__(/*! ../internals/regexp-sticky-helpers */ "./node_modules/core-js/internals/regexp-sticky-helpers.js");
var shared = __webpack_require__(/*! ../internals/shared */ "./node_modules/core-js/internals/shared.js");
var create = __webpack_require__(/*! ../internals/object-create */ "./node_modules/core-js/internals/object-create.js");
var getInternalState = (__webpack_require__(/*! ../internals/internal-state */ "./node_modules/core-js/internals/internal-state.js").get);
var UNSUPPORTED_DOT_ALL = __webpack_require__(/*! ../internals/regexp-unsupported-dot-all */ "./node_modules/core-js/internals/regexp-unsupported-dot-all.js");
var UNSUPPORTED_NCG = __webpack_require__(/*! ../internals/regexp-unsupported-ncg */ "./node_modules/core-js/internals/regexp-unsupported-ncg.js");

var nativeReplace = shared('native-string-replace', String.prototype.replace);
var nativeExec = RegExp.prototype.exec;
var patchedExec = nativeExec;
var charAt = uncurryThis(''.charAt);
var indexOf = uncurryThis(''.indexOf);
var replace = uncurryThis(''.replace);
var stringSlice = uncurryThis(''.slice);

var UPDATES_LAST_INDEX_WRONG = (function () {
  var re1 = /a/;
  var re2 = /b*/g;
  call(nativeExec, re1, 'a');
  call(nativeExec, re2, 'a');
  return re1.lastIndex !== 0 || re2.lastIndex !== 0;
})();

var UNSUPPORTED_Y = stickyHelpers.BROKEN_CARET;

// nonparticipating capturing group, copied from es5-shim's String#split patch.
var NPCG_INCLUDED = /()??/.exec('')[1] !== undefined;

var PATCH = UPDATES_LAST_INDEX_WRONG || NPCG_INCLUDED || UNSUPPORTED_Y || UNSUPPORTED_DOT_ALL || UNSUPPORTED_NCG;

if (PATCH) {
  patchedExec = function exec(string) {
    var re = this;
    var state = getInternalState(re);
    var str = toString(string);
    var raw = state.raw;
    var result, reCopy, lastIndex, match, i, object, group;

    if (raw) {
      raw.lastIndex = re.lastIndex;
      result = call(patchedExec, raw, str);
      re.lastIndex = raw.lastIndex;
      return result;
    }

    var groups = state.groups;
    var sticky = UNSUPPORTED_Y && re.sticky;
    var flags = call(regexpFlags, re);
    var source = re.source;
    var charsAdded = 0;
    var strCopy = str;

    if (sticky) {
      flags = replace(flags, 'y', '');
      if (indexOf(flags, 'g') === -1) {
        flags += 'g';
      }

      strCopy = stringSlice(str, re.lastIndex);
      // Support anchored sticky behavior.
      if (re.lastIndex > 0 && (!re.multiline || re.multiline && charAt(str, re.lastIndex - 1) !== '\n')) {
        source = '(?: ' + source + ')';
        strCopy = ' ' + strCopy;
        charsAdded++;
      }
      // ^(? + rx + ) is needed, in combination with some str slicing, to
      // simulate the 'y' flag.
      reCopy = new RegExp('^(?:' + source + ')', flags);
    }

    if (NPCG_INCLUDED) {
      reCopy = new RegExp('^' + source + '$(?!\\s)', flags);
    }
    if (UPDATES_LAST_INDEX_WRONG) lastIndex = re.lastIndex;

    match = call(nativeExec, sticky ? reCopy : re, strCopy);

    if (sticky) {
      if (match) {
        match.input = stringSlice(match.input, charsAdded);
        match[0] = stringSlice(match[0], charsAdded);
        match.index = re.lastIndex;
        re.lastIndex += match[0].length;
      } else re.lastIndex = 0;
    } else if (UPDATES_LAST_INDEX_WRONG && match) {
      re.lastIndex = re.global ? match.index + match[0].length : lastIndex;
    }
    if (NPCG_INCLUDED && match && match.length > 1) {
      // Fix browsers whose `exec` methods don't consistently return `undefined`
      // for NPCG, like IE8. NOTE: This doesn't work for /(.?)?/
      call(nativeReplace, match[0], reCopy, function () {
        for (i = 1; i < arguments.length - 2; i++) {
          if (arguments[i] === undefined) match[i] = undefined;
        }
      });
    }

    if (match && groups) {
      match.groups = object = create(null);
      for (i = 0; i < groups.length; i++) {
        group = groups[i];
        object[group[0]] = match[group[1]];
      }
    }

    return match;
  };
}

module.exports = patchedExec;


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-flags-detection.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-flags-detection.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");

// babel-minify and Closure Compiler transpiles RegExp('.', 'd') -> /./d and it causes SyntaxError
var RegExp = globalThis.RegExp;

var FLAGS_GETTER_IS_CORRECT = !fails(function () {
  var INDICES_SUPPORT = true;
  try {
    RegExp('.', 'd');
  } catch (error) {
    INDICES_SUPPORT = false;
  }

  var O = {};
  // modern V8 bug
  var calls = '';
  var expected = INDICES_SUPPORT ? 'dgimsy' : 'gimsy';

  var addGetter = function (key, chr) {
    // eslint-disable-next-line es/no-object-defineproperty -- safe
    Object.defineProperty(O, key, { get: function () {
      calls += chr;
      return true;
    } });
  };

  var pairs = {
    dotAll: 's',
    global: 'g',
    ignoreCase: 'i',
    multiline: 'm',
    sticky: 'y'
  };

  if (INDICES_SUPPORT) pairs.hasIndices = 'd';

  for (var key in pairs) addGetter(key, pairs[key]);

  // eslint-disable-next-line es/no-object-getownpropertydescriptor -- safe
  var result = Object.getOwnPropertyDescriptor(RegExp.prototype, 'flags').get.call(O);

  return result !== expected || calls !== expected;
});

module.exports = { correct: FLAGS_GETTER_IS_CORRECT };


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-flags.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-flags.js ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");

// `RegExp.prototype.flags` getter implementation
// https://tc39.es/ecma262/#sec-get-regexp.prototype.flags
module.exports = function () {
  var that = anObject(this);
  var result = '';
  if (that.hasIndices) result += 'd';
  if (that.global) result += 'g';
  if (that.ignoreCase) result += 'i';
  if (that.multiline) result += 'm';
  if (that.dotAll) result += 's';
  if (that.unicode) result += 'u';
  if (that.unicodeSets) result += 'v';
  if (that.sticky) result += 'y';
  return result;
};


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-get-flags.js":
/*!************************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-get-flags.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var isPrototypeOf = __webpack_require__(/*! ../internals/object-is-prototype-of */ "./node_modules/core-js/internals/object-is-prototype-of.js");
var regExpFlagsDetection = __webpack_require__(/*! ../internals/regexp-flags-detection */ "./node_modules/core-js/internals/regexp-flags-detection.js");
var regExpFlagsGetterImplementation = __webpack_require__(/*! ../internals/regexp-flags */ "./node_modules/core-js/internals/regexp-flags.js");

var RegExpPrototype = RegExp.prototype;

module.exports = regExpFlagsDetection.correct ? function (it) {
  return it.flags;
} : function (it) {
  return (!regExpFlagsDetection.correct && isPrototypeOf(RegExpPrototype, it) && !hasOwn(it, 'flags'))
    ? call(regExpFlagsGetterImplementation, it)
    : it.flags;
};


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-sticky-helpers.js":
/*!*****************************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-sticky-helpers.js ***!
  \*****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");

// babel-minify and Closure Compiler transpiles RegExp('a', 'y') -> /a/y and it causes SyntaxError
var $RegExp = globalThis.RegExp;

var UNSUPPORTED_Y = fails(function () {
  var re = $RegExp('a', 'y');
  re.lastIndex = 2;
  return re.exec('abcd') !== null;
});

// UC Browser bug
// https://github.com/zloirock/core-js/issues/1008
var MISSED_STICKY = UNSUPPORTED_Y || fails(function () {
  return !$RegExp('a', 'y').sticky;
});

var BROKEN_CARET = UNSUPPORTED_Y || fails(function () {
  // https://bugzilla.mozilla.org/show_bug.cgi?id=773687
  var re = $RegExp('^r', 'gy');
  re.lastIndex = 2;
  return re.exec('str') !== null;
});

module.exports = {
  BROKEN_CARET: BROKEN_CARET,
  MISSED_STICKY: MISSED_STICKY,
  UNSUPPORTED_Y: UNSUPPORTED_Y
};


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-unsupported-dot-all.js":
/*!**********************************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-unsupported-dot-all.js ***!
  \**********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");

// babel-minify and Closure Compiler transpiles RegExp('.', 's') -> /./s and it causes SyntaxError
var $RegExp = globalThis.RegExp;

module.exports = fails(function () {
  var re = $RegExp('.', 's');
  return !(re.dotAll && re.test('\n') && re.flags === 's');
});


/***/ }),

/***/ "./node_modules/core-js/internals/regexp-unsupported-ncg.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/regexp-unsupported-ncg.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");

// babel-minify and Closure Compiler transpiles RegExp('(?<a>b)', 'g') -> /(?<a>b)/g and it causes SyntaxError
var $RegExp = globalThis.RegExp;

module.exports = fails(function () {
  var re = $RegExp('(?<a>b)', 'g');
  return re.exec('b').groups.a !== 'b' ||
    'b'.replace(re, '$<a>c') !== 'bc';
});


/***/ }),

/***/ "./node_modules/core-js/internals/require-object-coercible.js":
/*!********************************************************************!*\
  !*** ./node_modules/core-js/internals/require-object-coercible.js ***!
  \********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var isNullOrUndefined = __webpack_require__(/*! ../internals/is-null-or-undefined */ "./node_modules/core-js/internals/is-null-or-undefined.js");

var $TypeError = TypeError;

// `RequireObjectCoercible` abstract operation
// https://tc39.es/ecma262/#sec-requireobjectcoercible
module.exports = function (it) {
  if (isNullOrUndefined(it)) throw new $TypeError("Can't call method on " + it);
  return it;
};


/***/ }),

/***/ "./node_modules/core-js/internals/set-species.js":
/*!*******************************************************!*\
  !*** ./node_modules/core-js/internals/set-species.js ***!
  \*******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "./node_modules/core-js/internals/get-built-in.js");
var defineBuiltInAccessor = __webpack_require__(/*! ../internals/define-built-in-accessor */ "./node_modules/core-js/internals/define-built-in-accessor.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");
var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");

var SPECIES = wellKnownSymbol('species');

module.exports = function (CONSTRUCTOR_NAME) {
  var Constructor = getBuiltIn(CONSTRUCTOR_NAME);

  if (DESCRIPTORS && Constructor && !Constructor[SPECIES]) {
    defineBuiltInAccessor(Constructor, SPECIES, {
      configurable: true,
      get: function () { return this; }
    });
  }
};


/***/ }),

/***/ "./node_modules/core-js/internals/set-to-string-tag.js":
/*!*************************************************************!*\
  !*** ./node_modules/core-js/internals/set-to-string-tag.js ***!
  \*************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var defineProperty = (__webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js").f);
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var TO_STRING_TAG = wellKnownSymbol('toStringTag');

module.exports = function (target, TAG, STATIC) {
  if (target && !STATIC) target = target.prototype;
  if (target && !hasOwn(target, TO_STRING_TAG)) {
    defineProperty(target, TO_STRING_TAG, { configurable: true, value: TAG });
  }
};


/***/ }),

/***/ "./node_modules/core-js/internals/shared-key.js":
/*!******************************************************!*\
  !*** ./node_modules/core-js/internals/shared-key.js ***!
  \******************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var shared = __webpack_require__(/*! ../internals/shared */ "./node_modules/core-js/internals/shared.js");
var uid = __webpack_require__(/*! ../internals/uid */ "./node_modules/core-js/internals/uid.js");

var keys = shared('keys');

module.exports = function (key) {
  return keys[key] || (keys[key] = uid(key));
};


/***/ }),

/***/ "./node_modules/core-js/internals/shared-store.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/internals/shared-store.js ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "./node_modules/core-js/internals/is-pure.js");
var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var defineGlobalProperty = __webpack_require__(/*! ../internals/define-global-property */ "./node_modules/core-js/internals/define-global-property.js");

var SHARED = '__core-js_shared__';
var store = module.exports = globalThis[SHARED] || defineGlobalProperty(SHARED, {});

(store.versions || (store.versions = [])).push({
  version: '3.47.0',
  mode: IS_PURE ? 'pure' : 'global',
  copyright: '© 2014-2025 Denis Pushkarev (zloirock.ru), 2025 CoreJS Company (core-js.io)',
  license: 'https://github.com/zloirock/core-js/blob/v3.47.0/LICENSE',
  source: 'https://github.com/zloirock/core-js'
});


/***/ }),

/***/ "./node_modules/core-js/internals/shared.js":
/*!**************************************************!*\
  !*** ./node_modules/core-js/internals/shared.js ***!
  \**************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var store = __webpack_require__(/*! ../internals/shared-store */ "./node_modules/core-js/internals/shared-store.js");

module.exports = function (key, value) {
  return store[key] || (store[key] = value || {});
};


/***/ }),

/***/ "./node_modules/core-js/internals/species-constructor.js":
/*!***************************************************************!*\
  !*** ./node_modules/core-js/internals/species-constructor.js ***!
  \***************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");
var aConstructor = __webpack_require__(/*! ../internals/a-constructor */ "./node_modules/core-js/internals/a-constructor.js");
var isNullOrUndefined = __webpack_require__(/*! ../internals/is-null-or-undefined */ "./node_modules/core-js/internals/is-null-or-undefined.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var SPECIES = wellKnownSymbol('species');

// `SpeciesConstructor` abstract operation
// https://tc39.es/ecma262/#sec-speciesconstructor
module.exports = function (O, defaultConstructor) {
  var C = anObject(O).constructor;
  var S;
  return C === undefined || isNullOrUndefined(S = anObject(C)[SPECIES]) ? defaultConstructor : aConstructor(S);
};


/***/ }),

/***/ "./node_modules/core-js/internals/string-multibyte.js":
/*!************************************************************!*\
  !*** ./node_modules/core-js/internals/string-multibyte.js ***!
  \************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var toIntegerOrInfinity = __webpack_require__(/*! ../internals/to-integer-or-infinity */ "./node_modules/core-js/internals/to-integer-or-infinity.js");
var toString = __webpack_require__(/*! ../internals/to-string */ "./node_modules/core-js/internals/to-string.js");
var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "./node_modules/core-js/internals/require-object-coercible.js");

var charAt = uncurryThis(''.charAt);
var charCodeAt = uncurryThis(''.charCodeAt);
var stringSlice = uncurryThis(''.slice);

var createMethod = function (CONVERT_TO_STRING) {
  return function ($this, pos) {
    var S = toString(requireObjectCoercible($this));
    var position = toIntegerOrInfinity(pos);
    var size = S.length;
    var first, second;
    if (position < 0 || position >= size) return CONVERT_TO_STRING ? '' : undefined;
    first = charCodeAt(S, position);
    return first < 0xD800 || first > 0xDBFF || position + 1 === size
      || (second = charCodeAt(S, position + 1)) < 0xDC00 || second > 0xDFFF
        ? CONVERT_TO_STRING
          ? charAt(S, position)
          : first
        : CONVERT_TO_STRING
          ? stringSlice(S, position, position + 2)
          : (first - 0xD800 << 10) + (second - 0xDC00) + 0x10000;
  };
};

module.exports = {
  // `String.prototype.codePointAt` method
  // https://tc39.es/ecma262/#sec-string.prototype.codepointat
  codeAt: createMethod(false),
  // `String.prototype.at` method
  // https://github.com/mathiasbynens/String.prototype.at
  charAt: createMethod(true)
};


/***/ }),

/***/ "./node_modules/core-js/internals/symbol-constructor-detection.js":
/*!************************************************************************!*\
  !*** ./node_modules/core-js/internals/symbol-constructor-detection.js ***!
  \************************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

/* eslint-disable es/no-symbol -- required for testing */
var V8_VERSION = __webpack_require__(/*! ../internals/environment-v8-version */ "./node_modules/core-js/internals/environment-v8-version.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");

var $String = globalThis.String;

// eslint-disable-next-line es/no-object-getownpropertysymbols -- required for testing
module.exports = !!Object.getOwnPropertySymbols && !fails(function () {
  var symbol = Symbol('symbol detection');
  // Chrome 38 Symbol has incorrect toString conversion
  // `get-own-property-symbols` polyfill symbols converted to object are not Symbol instances
  // nb: Do not call `String` directly to avoid this being optimized out to `symbol+''` which will,
  // of course, fail.
  return !$String(symbol) || !(Object(symbol) instanceof Symbol) ||
    // Chrome 38-40 symbols are not inherited from DOM collections prototypes to instances
    !Symbol.sham && V8_VERSION && V8_VERSION < 41;
});


/***/ }),

/***/ "./node_modules/core-js/internals/to-absolute-index.js":
/*!*************************************************************!*\
  !*** ./node_modules/core-js/internals/to-absolute-index.js ***!
  \*************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var toIntegerOrInfinity = __webpack_require__(/*! ../internals/to-integer-or-infinity */ "./node_modules/core-js/internals/to-integer-or-infinity.js");

var max = Math.max;
var min = Math.min;

// Helper for a popular repeating case of the spec:
// Let integer be ? ToInteger(index).
// If integer < 0, let result be max((length + integer), 0); else let result be min(integer, length).
module.exports = function (index, length) {
  var integer = toIntegerOrInfinity(index);
  return integer < 0 ? max(integer + length, 0) : min(integer, length);
};


/***/ }),

/***/ "./node_modules/core-js/internals/to-indexed-object.js":
/*!*************************************************************!*\
  !*** ./node_modules/core-js/internals/to-indexed-object.js ***!
  \*************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

// toObject with fallback for non-array-like ES3 strings
var IndexedObject = __webpack_require__(/*! ../internals/indexed-object */ "./node_modules/core-js/internals/indexed-object.js");
var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "./node_modules/core-js/internals/require-object-coercible.js");

module.exports = function (it) {
  return IndexedObject(requireObjectCoercible(it));
};


/***/ }),

/***/ "./node_modules/core-js/internals/to-integer-or-infinity.js":
/*!******************************************************************!*\
  !*** ./node_modules/core-js/internals/to-integer-or-infinity.js ***!
  \******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var trunc = __webpack_require__(/*! ../internals/math-trunc */ "./node_modules/core-js/internals/math-trunc.js");

// `ToIntegerOrInfinity` abstract operation
// https://tc39.es/ecma262/#sec-tointegerorinfinity
module.exports = function (argument) {
  var number = +argument;
  // eslint-disable-next-line no-self-compare -- NaN check
  return number !== number || number === 0 ? 0 : trunc(number);
};


/***/ }),

/***/ "./node_modules/core-js/internals/to-length.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/to-length.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var toIntegerOrInfinity = __webpack_require__(/*! ../internals/to-integer-or-infinity */ "./node_modules/core-js/internals/to-integer-or-infinity.js");

var min = Math.min;

// `ToLength` abstract operation
// https://tc39.es/ecma262/#sec-tolength
module.exports = function (argument) {
  var len = toIntegerOrInfinity(argument);
  return len > 0 ? min(len, 0x1FFFFFFFFFFFFF) : 0; // 2 ** 53 - 1 == 9007199254740991
};


/***/ }),

/***/ "./node_modules/core-js/internals/to-object.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/to-object.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "./node_modules/core-js/internals/require-object-coercible.js");

var $Object = Object;

// `ToObject` abstract operation
// https://tc39.es/ecma262/#sec-toobject
module.exports = function (argument) {
  return $Object(requireObjectCoercible(argument));
};


/***/ }),

/***/ "./node_modules/core-js/internals/to-primitive.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/internals/to-primitive.js ***!
  \********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var isSymbol = __webpack_require__(/*! ../internals/is-symbol */ "./node_modules/core-js/internals/is-symbol.js");
var getMethod = __webpack_require__(/*! ../internals/get-method */ "./node_modules/core-js/internals/get-method.js");
var ordinaryToPrimitive = __webpack_require__(/*! ../internals/ordinary-to-primitive */ "./node_modules/core-js/internals/ordinary-to-primitive.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var $TypeError = TypeError;
var TO_PRIMITIVE = wellKnownSymbol('toPrimitive');

// `ToPrimitive` abstract operation
// https://tc39.es/ecma262/#sec-toprimitive
module.exports = function (input, pref) {
  if (!isObject(input) || isSymbol(input)) return input;
  var exoticToPrim = getMethod(input, TO_PRIMITIVE);
  var result;
  if (exoticToPrim) {
    if (pref === undefined) pref = 'default';
    result = call(exoticToPrim, input, pref);
    if (!isObject(result) || isSymbol(result)) return result;
    throw new $TypeError("Can't convert object to primitive value");
  }
  if (pref === undefined) pref = 'number';
  return ordinaryToPrimitive(input, pref);
};


/***/ }),

/***/ "./node_modules/core-js/internals/to-property-key.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/internals/to-property-key.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var toPrimitive = __webpack_require__(/*! ../internals/to-primitive */ "./node_modules/core-js/internals/to-primitive.js");
var isSymbol = __webpack_require__(/*! ../internals/is-symbol */ "./node_modules/core-js/internals/is-symbol.js");

// `ToPropertyKey` abstract operation
// https://tc39.es/ecma262/#sec-topropertykey
module.exports = function (argument) {
  var key = toPrimitive(argument, 'string');
  return isSymbol(key) ? key : key + '';
};


/***/ }),

/***/ "./node_modules/core-js/internals/to-string-tag-support.js":
/*!*****************************************************************!*\
  !*** ./node_modules/core-js/internals/to-string-tag-support.js ***!
  \*****************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var TO_STRING_TAG = wellKnownSymbol('toStringTag');
var test = {};
// eslint-disable-next-line unicorn/no-immediate-mutation -- ES3 syntax limitation
test[TO_STRING_TAG] = 'z';

module.exports = String(test) === '[object z]';


/***/ }),

/***/ "./node_modules/core-js/internals/to-string.js":
/*!*****************************************************!*\
  !*** ./node_modules/core-js/internals/to-string.js ***!
  \*****************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var classof = __webpack_require__(/*! ../internals/classof */ "./node_modules/core-js/internals/classof.js");

var $String = String;

module.exports = function (argument) {
  if (classof(argument) === 'Symbol') throw new TypeError('Cannot convert a Symbol value to a string');
  return $String(argument);
};


/***/ }),

/***/ "./node_modules/core-js/internals/try-to-string.js":
/*!*********************************************************!*\
  !*** ./node_modules/core-js/internals/try-to-string.js ***!
  \*********************************************************/
/***/ ((module) => {

"use strict";

var $String = String;

module.exports = function (argument) {
  try {
    return $String(argument);
  } catch (error) {
    return 'Object';
  }
};


/***/ }),

/***/ "./node_modules/core-js/internals/uid.js":
/*!***********************************************!*\
  !*** ./node_modules/core-js/internals/uid.js ***!
  \***********************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");

var id = 0;
var postfix = Math.random();
var toString = uncurryThis(1.1.toString);

module.exports = function (key) {
  return 'Symbol(' + (key === undefined ? '' : key) + ')_' + toString(++id + postfix, 36);
};


/***/ }),

/***/ "./node_modules/core-js/internals/use-symbol-as-uid.js":
/*!*************************************************************!*\
  !*** ./node_modules/core-js/internals/use-symbol-as-uid.js ***!
  \*************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

/* eslint-disable es/no-symbol -- required for testing */
var NATIVE_SYMBOL = __webpack_require__(/*! ../internals/symbol-constructor-detection */ "./node_modules/core-js/internals/symbol-constructor-detection.js");

module.exports = NATIVE_SYMBOL &&
  !Symbol.sham &&
  typeof Symbol.iterator == 'symbol';


/***/ }),

/***/ "./node_modules/core-js/internals/v8-prototype-define-bug.js":
/*!*******************************************************************!*\
  !*** ./node_modules/core-js/internals/v8-prototype-define-bug.js ***!
  \*******************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");

// V8 ~ Chrome 36-
// https://bugs.chromium.org/p/v8/issues/detail?id=3334
module.exports = DESCRIPTORS && fails(function () {
  // eslint-disable-next-line es/no-object-defineproperty -- required for testing
  return Object.defineProperty(function () { /* empty */ }, 'prototype', {
    value: 42,
    writable: false
  }).prototype !== 42;
});


/***/ }),

/***/ "./node_modules/core-js/internals/weak-map-basic-detection.js":
/*!********************************************************************!*\
  !*** ./node_modules/core-js/internals/weak-map-basic-detection.js ***!
  \********************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");

var WeakMap = globalThis.WeakMap;

module.exports = isCallable(WeakMap) && /native code/.test(String(WeakMap));


/***/ }),

/***/ "./node_modules/core-js/internals/well-known-symbol.js":
/*!*************************************************************!*\
  !*** ./node_modules/core-js/internals/well-known-symbol.js ***!
  \*************************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var shared = __webpack_require__(/*! ../internals/shared */ "./node_modules/core-js/internals/shared.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var uid = __webpack_require__(/*! ../internals/uid */ "./node_modules/core-js/internals/uid.js");
var NATIVE_SYMBOL = __webpack_require__(/*! ../internals/symbol-constructor-detection */ "./node_modules/core-js/internals/symbol-constructor-detection.js");
var USE_SYMBOL_AS_UID = __webpack_require__(/*! ../internals/use-symbol-as-uid */ "./node_modules/core-js/internals/use-symbol-as-uid.js");

var Symbol = globalThis.Symbol;
var WellKnownSymbolsStore = shared('wks');
var createWellKnownSymbol = USE_SYMBOL_AS_UID ? Symbol['for'] || Symbol : Symbol && Symbol.withoutSetter || uid;

module.exports = function (name) {
  if (!hasOwn(WellKnownSymbolsStore, name)) {
    WellKnownSymbolsStore[name] = NATIVE_SYMBOL && hasOwn(Symbol, name)
      ? Symbol[name]
      : createWellKnownSymbol('Symbol.' + name);
  } return WellKnownSymbolsStore[name];
};


/***/ }),

/***/ "./node_modules/core-js/modules/es.array.iterator.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/modules/es.array.iterator.js ***!
  \***********************************************************/
/***/ ((module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var toIndexedObject = __webpack_require__(/*! ../internals/to-indexed-object */ "./node_modules/core-js/internals/to-indexed-object.js");
var addToUnscopables = __webpack_require__(/*! ../internals/add-to-unscopables */ "./node_modules/core-js/internals/add-to-unscopables.js");
var Iterators = __webpack_require__(/*! ../internals/iterators */ "./node_modules/core-js/internals/iterators.js");
var InternalStateModule = __webpack_require__(/*! ../internals/internal-state */ "./node_modules/core-js/internals/internal-state.js");
var defineProperty = (__webpack_require__(/*! ../internals/object-define-property */ "./node_modules/core-js/internals/object-define-property.js").f);
var defineIterator = __webpack_require__(/*! ../internals/iterator-define */ "./node_modules/core-js/internals/iterator-define.js");
var createIterResultObject = __webpack_require__(/*! ../internals/create-iter-result-object */ "./node_modules/core-js/internals/create-iter-result-object.js");
var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "./node_modules/core-js/internals/is-pure.js");
var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");

var ARRAY_ITERATOR = 'Array Iterator';
var setInternalState = InternalStateModule.set;
var getInternalState = InternalStateModule.getterFor(ARRAY_ITERATOR);

// `Array.prototype.entries` method
// https://tc39.es/ecma262/#sec-array.prototype.entries
// `Array.prototype.keys` method
// https://tc39.es/ecma262/#sec-array.prototype.keys
// `Array.prototype.values` method
// https://tc39.es/ecma262/#sec-array.prototype.values
// `Array.prototype[@@iterator]` method
// https://tc39.es/ecma262/#sec-array.prototype-@@iterator
// `CreateArrayIterator` internal method
// https://tc39.es/ecma262/#sec-createarrayiterator
module.exports = defineIterator(Array, 'Array', function (iterated, kind) {
  setInternalState(this, {
    type: ARRAY_ITERATOR,
    target: toIndexedObject(iterated), // target
    index: 0,                          // next index
    kind: kind                         // kind
  });
// `%ArrayIteratorPrototype%.next` method
// https://tc39.es/ecma262/#sec-%arrayiteratorprototype%.next
}, function () {
  var state = getInternalState(this);
  var target = state.target;
  var index = state.index++;
  if (!target || index >= target.length) {
    state.target = null;
    return createIterResultObject(undefined, true);
  }
  switch (state.kind) {
    case 'keys': return createIterResultObject(index, false);
    case 'values': return createIterResultObject(target[index], false);
  } return createIterResultObject([index, target[index]], false);
}, 'values');

// argumentsList[@@iterator] is %ArrayProto_values%
// https://tc39.es/ecma262/#sec-createunmappedargumentsobject
// https://tc39.es/ecma262/#sec-createmappedargumentsobject
var values = Iterators.Arguments = Iterators.Array;

// https://tc39.es/ecma262/#sec-array.prototype-@@unscopables
addToUnscopables('keys');
addToUnscopables('values');
addToUnscopables('entries');

// V8 ~ Chrome 45- bug
if (!IS_PURE && DESCRIPTORS && values.name !== 'values') try {
  defineProperty(values, 'name', { value: 'values' });
} catch (error) { /* empty */ }


/***/ }),

/***/ "./node_modules/core-js/modules/es.promise.finally.js":
/*!************************************************************!*\
  !*** ./node_modules/core-js/modules/es.promise.finally.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var IS_PURE = __webpack_require__(/*! ../internals/is-pure */ "./node_modules/core-js/internals/is-pure.js");
var NativePromiseConstructor = __webpack_require__(/*! ../internals/promise-native-constructor */ "./node_modules/core-js/internals/promise-native-constructor.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var getBuiltIn = __webpack_require__(/*! ../internals/get-built-in */ "./node_modules/core-js/internals/get-built-in.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var speciesConstructor = __webpack_require__(/*! ../internals/species-constructor */ "./node_modules/core-js/internals/species-constructor.js");
var promiseResolve = __webpack_require__(/*! ../internals/promise-resolve */ "./node_modules/core-js/internals/promise-resolve.js");
var defineBuiltIn = __webpack_require__(/*! ../internals/define-built-in */ "./node_modules/core-js/internals/define-built-in.js");

var NativePromisePrototype = NativePromiseConstructor && NativePromiseConstructor.prototype;

// Safari bug https://bugs.webkit.org/show_bug.cgi?id=200829
var NON_GENERIC = !!NativePromiseConstructor && fails(function () {
  // eslint-disable-next-line unicorn/no-thenable -- required for testing
  NativePromisePrototype['finally'].call({ then: function () { /* empty */ } }, function () { /* empty */ });
});

// `Promise.prototype.finally` method
// https://tc39.es/ecma262/#sec-promise.prototype.finally
$({ target: 'Promise', proto: true, real: true, forced: NON_GENERIC }, {
  'finally': function (onFinally) {
    var C = speciesConstructor(this, getBuiltIn('Promise'));
    var isFunction = isCallable(onFinally);
    return this.then(
      isFunction ? function (x) {
        return promiseResolve(C, onFinally()).then(function () { return x; });
      } : onFinally,
      isFunction ? function (e) {
        return promiseResolve(C, onFinally()).then(function () { throw e; });
      } : onFinally
    );
  }
});

// makes sure that native promise-based APIs `Promise#finally` properly works with patched `Promise#then`
if (!IS_PURE && isCallable(NativePromiseConstructor)) {
  var method = getBuiltIn('Promise').prototype['finally'];
  if (NativePromisePrototype['finally'] !== method) {
    defineBuiltIn(NativePromisePrototype, 'finally', method, { unsafe: true });
  }
}


/***/ }),

/***/ "./node_modules/core-js/modules/es.regexp.constructor.js":
/*!***************************************************************!*\
  !*** ./node_modules/core-js/modules/es.regexp.constructor.js ***!
  \***************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var DESCRIPTORS = __webpack_require__(/*! ../internals/descriptors */ "./node_modules/core-js/internals/descriptors.js");
var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var isForced = __webpack_require__(/*! ../internals/is-forced */ "./node_modules/core-js/internals/is-forced.js");
var inheritIfRequired = __webpack_require__(/*! ../internals/inherit-if-required */ "./node_modules/core-js/internals/inherit-if-required.js");
var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "./node_modules/core-js/internals/create-non-enumerable-property.js");
var create = __webpack_require__(/*! ../internals/object-create */ "./node_modules/core-js/internals/object-create.js");
var getOwnPropertyNames = (__webpack_require__(/*! ../internals/object-get-own-property-names */ "./node_modules/core-js/internals/object-get-own-property-names.js").f);
var isPrototypeOf = __webpack_require__(/*! ../internals/object-is-prototype-of */ "./node_modules/core-js/internals/object-is-prototype-of.js");
var isRegExp = __webpack_require__(/*! ../internals/is-regexp */ "./node_modules/core-js/internals/is-regexp.js");
var toString = __webpack_require__(/*! ../internals/to-string */ "./node_modules/core-js/internals/to-string.js");
var getRegExpFlags = __webpack_require__(/*! ../internals/regexp-get-flags */ "./node_modules/core-js/internals/regexp-get-flags.js");
var stickyHelpers = __webpack_require__(/*! ../internals/regexp-sticky-helpers */ "./node_modules/core-js/internals/regexp-sticky-helpers.js");
var proxyAccessor = __webpack_require__(/*! ../internals/proxy-accessor */ "./node_modules/core-js/internals/proxy-accessor.js");
var defineBuiltIn = __webpack_require__(/*! ../internals/define-built-in */ "./node_modules/core-js/internals/define-built-in.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var hasOwn = __webpack_require__(/*! ../internals/has-own-property */ "./node_modules/core-js/internals/has-own-property.js");
var enforceInternalState = (__webpack_require__(/*! ../internals/internal-state */ "./node_modules/core-js/internals/internal-state.js").enforce);
var setSpecies = __webpack_require__(/*! ../internals/set-species */ "./node_modules/core-js/internals/set-species.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");
var UNSUPPORTED_DOT_ALL = __webpack_require__(/*! ../internals/regexp-unsupported-dot-all */ "./node_modules/core-js/internals/regexp-unsupported-dot-all.js");
var UNSUPPORTED_NCG = __webpack_require__(/*! ../internals/regexp-unsupported-ncg */ "./node_modules/core-js/internals/regexp-unsupported-ncg.js");

var MATCH = wellKnownSymbol('match');
var NativeRegExp = globalThis.RegExp;
var RegExpPrototype = NativeRegExp.prototype;
var SyntaxError = globalThis.SyntaxError;
var exec = uncurryThis(RegExpPrototype.exec);
var charAt = uncurryThis(''.charAt);
var replace = uncurryThis(''.replace);
var stringIndexOf = uncurryThis(''.indexOf);
var stringSlice = uncurryThis(''.slice);
// TODO: Use only proper RegExpIdentifierName
var IS_NCG = /^\?<[^\s\d!#%&*+<=>@^][^\s!#%&*+<=>@^]*>/;
var re1 = /a/g;
var re2 = /a/g;

// "new" should create a new object, old webkit bug
var CORRECT_NEW = new NativeRegExp(re1) !== re1;

var MISSED_STICKY = stickyHelpers.MISSED_STICKY;
var UNSUPPORTED_Y = stickyHelpers.UNSUPPORTED_Y;

var BASE_FORCED = DESCRIPTORS &&
  (!CORRECT_NEW || MISSED_STICKY || UNSUPPORTED_DOT_ALL || UNSUPPORTED_NCG || fails(function () {
    re2[MATCH] = false;
    // RegExp constructor can alter flags and IsRegExp works correct with @@match
    // eslint-disable-next-line sonarjs/inconsistent-function-call -- required for testing
    return NativeRegExp(re1) !== re1 || NativeRegExp(re2) === re2 || String(NativeRegExp(re1, 'i')) !== '/a/i';
  }));

var handleDotAll = function (string) {
  var length = string.length;
  var index = 0;
  var result = '';
  var brackets = false;
  var chr;
  for (; index <= length; index++) {
    chr = charAt(string, index);
    if (chr === '\\') {
      result += chr + charAt(string, ++index);
      continue;
    }
    if (!brackets && chr === '.') {
      result += '[\\s\\S]';
    } else {
      if (chr === '[') {
        brackets = true;
      } else if (chr === ']') {
        brackets = false;
      } result += chr;
    }
  } return result;
};

var handleNCG = function (string) {
  var length = string.length;
  var index = 0;
  var result = '';
  var named = [];
  var names = create(null);
  var brackets = false;
  var ncg = false;
  var groupid = 0;
  var groupname = '';
  var chr;
  for (; index <= length; index++) {
    chr = charAt(string, index);
    if (chr === '\\') {
      chr += charAt(string, ++index);
    } else if (chr === ']') {
      brackets = false;
    } else if (!brackets) switch (true) {
      case chr === '[':
        brackets = true;
        break;
      case chr === '(':
        result += chr;
        // ignore non-capturing groups
        if (stringSlice(string, index + 1, index + 3) === '?:') {
          continue;
        }
        if (exec(IS_NCG, stringSlice(string, index + 1))) {
          index += 2;
          ncg = true;
        }
        groupid++;
        continue;
      case chr === '>' && ncg:
        if (groupname === '' || hasOwn(names, groupname)) {
          throw new SyntaxError('Invalid capture group name');
        }
        names[groupname] = true;
        named[named.length] = [groupname, groupid];
        ncg = false;
        groupname = '';
        continue;
    }
    if (ncg) groupname += chr;
    else result += chr;
  } return [result, named];
};

// `RegExp` constructor
// https://tc39.es/ecma262/#sec-regexp-constructor
if (isForced('RegExp', BASE_FORCED)) {
  var RegExpWrapper = function RegExp(pattern, flags) {
    var thisIsRegExp = isPrototypeOf(RegExpPrototype, this);
    var patternIsRegExp = isRegExp(pattern);
    var flagsAreUndefined = flags === undefined;
    var groups = [];
    var rawPattern = pattern;
    var rawFlags, dotAll, sticky, handled, result, state;

    if (!thisIsRegExp && patternIsRegExp && flagsAreUndefined && pattern.constructor === RegExpWrapper) {
      return pattern;
    }

    if (patternIsRegExp || isPrototypeOf(RegExpPrototype, pattern)) {
      pattern = pattern.source;
      if (flagsAreUndefined) flags = getRegExpFlags(rawPattern);
    }

    pattern = pattern === undefined ? '' : toString(pattern);
    flags = flags === undefined ? '' : toString(flags);
    rawPattern = pattern;

    if (UNSUPPORTED_DOT_ALL && 'dotAll' in re1) {
      dotAll = !!flags && stringIndexOf(flags, 's') > -1;
      if (dotAll) flags = replace(flags, /s/g, '');
    }

    rawFlags = flags;

    if (MISSED_STICKY && 'sticky' in re1) {
      sticky = !!flags && stringIndexOf(flags, 'y') > -1;
      if (sticky && UNSUPPORTED_Y) flags = replace(flags, /y/g, '');
    }

    if (UNSUPPORTED_NCG) {
      handled = handleNCG(pattern);
      pattern = handled[0];
      groups = handled[1];
    }

    result = inheritIfRequired(NativeRegExp(pattern, flags), thisIsRegExp ? this : RegExpPrototype, RegExpWrapper);

    if (dotAll || sticky || groups.length) {
      state = enforceInternalState(result);
      if (dotAll) {
        state.dotAll = true;
        state.raw = RegExpWrapper(handleDotAll(pattern), rawFlags);
      }
      if (sticky) state.sticky = true;
      if (groups.length) state.groups = groups;
    }

    if (pattern !== rawPattern) try {
      // fails in old engines, but we have no alternatives for unsupported regex syntax
      createNonEnumerableProperty(result, 'source', rawPattern === '' ? '(?:)' : rawPattern);
    } catch (error) { /* empty */ }

    return result;
  };

  for (var keys = getOwnPropertyNames(NativeRegExp), index = 0; keys.length > index;) {
    proxyAccessor(RegExpWrapper, NativeRegExp, keys[index++]);
  }

  RegExpPrototype.constructor = RegExpWrapper;
  RegExpWrapper.prototype = RegExpPrototype;
  defineBuiltIn(globalThis, 'RegExp', RegExpWrapper, { constructor: true });
}

// https://tc39.es/ecma262/#sec-get-regexp-@@species
setSpecies('RegExp');


/***/ }),

/***/ "./node_modules/core-js/modules/es.regexp.exec.js":
/*!********************************************************!*\
  !*** ./node_modules/core-js/modules/es.regexp.exec.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var $ = __webpack_require__(/*! ../internals/export */ "./node_modules/core-js/internals/export.js");
var exec = __webpack_require__(/*! ../internals/regexp-exec */ "./node_modules/core-js/internals/regexp-exec.js");

// `RegExp.prototype.exec` method
// https://tc39.es/ecma262/#sec-regexp.prototype.exec
$({ target: 'RegExp', proto: true, forced: /./.exec !== exec }, {
  exec: exec
});


/***/ }),

/***/ "./node_modules/core-js/modules/es.string.replace.js":
/*!***********************************************************!*\
  !*** ./node_modules/core-js/modules/es.string.replace.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var apply = __webpack_require__(/*! ../internals/function-apply */ "./node_modules/core-js/internals/function-apply.js");
var call = __webpack_require__(/*! ../internals/function-call */ "./node_modules/core-js/internals/function-call.js");
var uncurryThis = __webpack_require__(/*! ../internals/function-uncurry-this */ "./node_modules/core-js/internals/function-uncurry-this.js");
var fixRegExpWellKnownSymbolLogic = __webpack_require__(/*! ../internals/fix-regexp-well-known-symbol-logic */ "./node_modules/core-js/internals/fix-regexp-well-known-symbol-logic.js");
var fails = __webpack_require__(/*! ../internals/fails */ "./node_modules/core-js/internals/fails.js");
var anObject = __webpack_require__(/*! ../internals/an-object */ "./node_modules/core-js/internals/an-object.js");
var isCallable = __webpack_require__(/*! ../internals/is-callable */ "./node_modules/core-js/internals/is-callable.js");
var isObject = __webpack_require__(/*! ../internals/is-object */ "./node_modules/core-js/internals/is-object.js");
var toIntegerOrInfinity = __webpack_require__(/*! ../internals/to-integer-or-infinity */ "./node_modules/core-js/internals/to-integer-or-infinity.js");
var toLength = __webpack_require__(/*! ../internals/to-length */ "./node_modules/core-js/internals/to-length.js");
var toString = __webpack_require__(/*! ../internals/to-string */ "./node_modules/core-js/internals/to-string.js");
var requireObjectCoercible = __webpack_require__(/*! ../internals/require-object-coercible */ "./node_modules/core-js/internals/require-object-coercible.js");
var advanceStringIndex = __webpack_require__(/*! ../internals/advance-string-index */ "./node_modules/core-js/internals/advance-string-index.js");
var getMethod = __webpack_require__(/*! ../internals/get-method */ "./node_modules/core-js/internals/get-method.js");
var getSubstitution = __webpack_require__(/*! ../internals/get-substitution */ "./node_modules/core-js/internals/get-substitution.js");
var getRegExpFlags = __webpack_require__(/*! ../internals/regexp-get-flags */ "./node_modules/core-js/internals/regexp-get-flags.js");
var regExpExec = __webpack_require__(/*! ../internals/regexp-exec-abstract */ "./node_modules/core-js/internals/regexp-exec-abstract.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var REPLACE = wellKnownSymbol('replace');
var max = Math.max;
var min = Math.min;
var concat = uncurryThis([].concat);
var push = uncurryThis([].push);
var stringIndexOf = uncurryThis(''.indexOf);
var stringSlice = uncurryThis(''.slice);

var maybeToString = function (it) {
  return it === undefined ? it : String(it);
};

// IE <= 11 replaces $0 with the whole match, as if it was $&
// https://stackoverflow.com/questions/6024666/getting-ie-to-replace-a-regex-with-the-literal-string-0
var REPLACE_KEEPS_$0 = (function () {
  // eslint-disable-next-line regexp/prefer-escape-replacement-dollar-char -- required for testing
  return 'a'.replace(/./, '$0') === '$0';
})();

// Safari <= 13.0.3(?) substitutes nth capture where n>m with an empty string
var REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE = (function () {
  if (/./[REPLACE]) {
    return /./[REPLACE]('a', '$0') === '';
  }
  return false;
})();

var REPLACE_SUPPORTS_NAMED_GROUPS = !fails(function () {
  var re = /./;
  re.exec = function () {
    var result = [];
    result.groups = { a: '7' };
    return result;
  };
  // eslint-disable-next-line regexp/no-useless-dollar-replacements -- false positive
  return ''.replace(re, '$<a>') !== '7';
});

// @@replace logic
fixRegExpWellKnownSymbolLogic('replace', function (_, nativeReplace, maybeCallNative) {
  var UNSAFE_SUBSTITUTE = REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE ? '$' : '$0';

  return [
    // `String.prototype.replace` method
    // https://tc39.es/ecma262/#sec-string.prototype.replace
    function replace(searchValue, replaceValue) {
      var O = requireObjectCoercible(this);
      var replacer = isObject(searchValue) ? getMethod(searchValue, REPLACE) : undefined;
      return replacer
        ? call(replacer, searchValue, O, replaceValue)
        : call(nativeReplace, toString(O), searchValue, replaceValue);
    },
    // `RegExp.prototype[@@replace]` method
    // https://tc39.es/ecma262/#sec-regexp.prototype-@@replace
    function (string, replaceValue) {
      var rx = anObject(this);
      var S = toString(string);

      if (
        typeof replaceValue == 'string' &&
        stringIndexOf(replaceValue, UNSAFE_SUBSTITUTE) === -1 &&
        stringIndexOf(replaceValue, '$<') === -1
      ) {
        var res = maybeCallNative(nativeReplace, rx, S, replaceValue);
        if (res.done) return res.value;
      }

      var functionalReplace = isCallable(replaceValue);
      if (!functionalReplace) replaceValue = toString(replaceValue);

      var flags = toString(getRegExpFlags(rx));
      var global = stringIndexOf(flags, 'g') !== -1;
      var fullUnicode;
      if (global) {
        fullUnicode = stringIndexOf(flags, 'u') !== -1;
        rx.lastIndex = 0;
      }

      var results = [];
      var result;
      while (true) {
        result = regExpExec(rx, S);
        if (result === null) break;

        push(results, result);
        if (!global) break;

        var matchStr = toString(result[0]);
        if (matchStr === '') rx.lastIndex = advanceStringIndex(S, toLength(rx.lastIndex), fullUnicode);
      }

      var accumulatedResult = '';
      var nextSourcePosition = 0;
      for (var i = 0; i < results.length; i++) {
        result = results[i];

        var matched = toString(result[0]);
        var position = max(min(toIntegerOrInfinity(result.index), S.length), 0);
        var captures = [];
        var replacement;
        // NOTE: This is equivalent to
        //   captures = result.slice(1).map(maybeToString)
        // but for some reason `nativeSlice.call(result, 1, result.length)` (called in
        // the slice polyfill when slicing native arrays) "doesn't work" in safari 9 and
        // causes a crash (https://pastebin.com/N21QzeQA) when trying to debug it.
        for (var j = 1; j < result.length; j++) push(captures, maybeToString(result[j]));
        var namedCaptures = result.groups;
        if (functionalReplace) {
          var replacerArgs = concat([matched], captures, position, S);
          if (namedCaptures !== undefined) push(replacerArgs, namedCaptures);
          replacement = toString(apply(replaceValue, undefined, replacerArgs));
        } else {
          replacement = getSubstitution(matched, S, position, captures, namedCaptures, replaceValue);
        }
        if (position >= nextSourcePosition) {
          accumulatedResult += stringSlice(S, nextSourcePosition, position) + replacement;
          nextSourcePosition = position + matched.length;
        }
      }

      return accumulatedResult + stringSlice(S, nextSourcePosition);
    }
  ];
}, !REPLACE_SUPPORTS_NAMED_GROUPS || !REPLACE_KEEPS_$0 || REGEXP_REPLACE_SUBSTITUTES_UNDEFINED_CAPTURE);


/***/ }),

/***/ "./node_modules/core-js/modules/web.dom-collections.iterator.js":
/*!**********************************************************************!*\
  !*** ./node_modules/core-js/modules/web.dom-collections.iterator.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

"use strict";

var globalThis = __webpack_require__(/*! ../internals/global-this */ "./node_modules/core-js/internals/global-this.js");
var DOMIterables = __webpack_require__(/*! ../internals/dom-iterables */ "./node_modules/core-js/internals/dom-iterables.js");
var DOMTokenListPrototype = __webpack_require__(/*! ../internals/dom-token-list-prototype */ "./node_modules/core-js/internals/dom-token-list-prototype.js");
var ArrayIteratorMethods = __webpack_require__(/*! ../modules/es.array.iterator */ "./node_modules/core-js/modules/es.array.iterator.js");
var createNonEnumerableProperty = __webpack_require__(/*! ../internals/create-non-enumerable-property */ "./node_modules/core-js/internals/create-non-enumerable-property.js");
var setToStringTag = __webpack_require__(/*! ../internals/set-to-string-tag */ "./node_modules/core-js/internals/set-to-string-tag.js");
var wellKnownSymbol = __webpack_require__(/*! ../internals/well-known-symbol */ "./node_modules/core-js/internals/well-known-symbol.js");

var ITERATOR = wellKnownSymbol('iterator');
var ArrayValues = ArrayIteratorMethods.values;

var handlePrototype = function (CollectionPrototype, COLLECTION_NAME) {
  if (CollectionPrototype) {
    // some Chrome versions have non-configurable methods on DOMTokenList
    if (CollectionPrototype[ITERATOR] !== ArrayValues) try {
      createNonEnumerableProperty(CollectionPrototype, ITERATOR, ArrayValues);
    } catch (error) {
      CollectionPrototype[ITERATOR] = ArrayValues;
    }
    setToStringTag(CollectionPrototype, COLLECTION_NAME, true);
    if (DOMIterables[COLLECTION_NAME]) for (var METHOD_NAME in ArrayIteratorMethods) {
      // some Chrome versions have non-configurable methods on DOMTokenList
      if (CollectionPrototype[METHOD_NAME] !== ArrayIteratorMethods[METHOD_NAME]) try {
        createNonEnumerableProperty(CollectionPrototype, METHOD_NAME, ArrayIteratorMethods[METHOD_NAME]);
      } catch (error) {
        CollectionPrototype[METHOD_NAME] = ArrayIteratorMethods[METHOD_NAME];
      }
    }
  }
};

for (var COLLECTION_NAME in DOMIterables) {
  handlePrototype(globalThis[COLLECTION_NAME] && globalThis[COLLECTION_NAME].prototype, COLLECTION_NAME);
}

handlePrototype(DOMTokenListPrototype, 'DOMTokenList');


/***/ }),

/***/ "./src/Actions.js":
/*!************************!*\
  !*** ./src/Actions.js ***!
  \************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "./node_modules/core-js/modules/web.dom-collections.iterator.js");
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./AjaxHelper.js */ "./src/AjaxHelper.js");
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./StateManager.js */ "./src/StateManager.js");

/**
 * Actions - Vanilla JS replacement for Vuex actions
 * Handles async operations and API calls
 */


const Actions = function () {
  'use strict';

  /**
   * Logger utility
   */
  function log() {
    if (console.ls && console.ls.log) {
      console.ls.log.apply(console.ls, arguments);
    }
  }

  /**
   * Trigger pjax refresh and update toggle key
   */
  function updatePjax() {
    $(document).trigger('pjax:refresh');
    _StateManager_js__WEBPACK_IMPORTED_MODULE_2__["default"].commit('newToggleKey');
  }

  /**
   * Fetch side menus from server
   * @returns {Promise}
   */
  function getSidemenus() {
    return new Promise(function (resolve, reject) {
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_1__["default"].get(window.SideMenuData.getMenuUrl, {
        position: 'side'
      }).then(function (result) {
        log('sidemenues', result);
        const newSidemenus = LS.ld.orderBy(result.data.menues, function (a) {
          return parseInt(a.order || 999999);
        }, ['desc']);
        _StateManager_js__WEBPACK_IMPORTED_MODULE_2__["default"].commit('updateSidemenus', newSidemenus);
        updatePjax();
        resolve(newSidemenus);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Fetch collapsed/quick menus from server
   * @returns {Promise}
   */
  function getCollapsedmenus() {
    return new Promise(function (resolve, reject) {
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_1__["default"].get(window.SideMenuData.getMenuUrl, {
        position: 'collapsed'
      }).then(function (result) {
        log('quickmenu', result);
        const newCollapsedmenus = LS.ld.orderBy(result.data.menues, function (a) {
          return parseInt(a.order || 999999);
        }, ['desc']);
        _StateManager_js__WEBPACK_IMPORTED_MODULE_2__["default"].commit('updateCollapsedmenus', newCollapsedmenus);
        updatePjax();
        resolve(newCollapsedmenus);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Fetch questions from server
   * @returns {Promise}
   */
  function getQuestions() {
    return new Promise(function (resolve, reject) {
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_1__["default"].get(window.SideMenuData.getQuestionsUrl).then(function (result) {
        log('Questions', result);
        const newQuestiongroups = result.data.groups;
        _StateManager_js__WEBPACK_IMPORTED_MODULE_2__["default"].commit('updateQuestiongroups', newQuestiongroups);
        updatePjax();
        resolve(newQuestiongroups);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Collect both side menus and collapsed menus
   * @returns {Promise}
   */
  function collectMenus() {
    return Promise.all([getSidemenus(), getCollapsedmenus()]);
  }

  /**
   * Toggle lock/unlock organizer setting
   * @returns {Promise}
   */
  function unlockLockOrganizer() {
    return new Promise(function (resolve, reject) {
      const value = _StateManager_js__WEBPACK_IMPORTED_MODULE_2__["default"].get('allowOrganizer') ? '0' : '1';
      _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_1__["default"].post(window.SideMenuData.unlockLockOrganizerUrl, {
        setting: 'lock_organizer',
        newValue: value
      }).then(function (result) {
        log('setUsersettingLog', result);
        _StateManager_js__WEBPACK_IMPORTED_MODULE_2__["default"].commit('setAllowOrganizer', parseInt(value));
        resolve(result);
      }).catch(function (error) {
        reject(error);
      });
    });
  }

  /**
   * Change current tab and reload data
   * @param {string} tab
   * @returns {Promise}
   */
  function changeCurrentTab(tab) {
    _StateManager_js__WEBPACK_IMPORTED_MODULE_2__["default"].commit('changeCurrentTab', tab);
    return Promise.all([collectMenus(), getQuestions()]);
  }

  /**
   * Update question group order on server
   * @param {Array} questiongroups
   * @param {string} surveyid
   * @returns {Promise}
   */
  function updateQuestionGroupOrder(questiongroups, surveyid) {
    const onlyGroupsArray = LS.ld.map(questiongroups, function (questiongroup) {
      const questions = LS.ld.map(questiongroup.questions, function (question) {
        return {
          qid: question.qid,
          question: question.question,
          gid: question.gid,
          question_order: question.question_order
        };
      });
      return {
        gid: questiongroup.gid,
        group_name: questiongroup.group_name,
        group_order: questiongroup.group_order,
        questions: questions
      };
    });
    return _AjaxHelper_js__WEBPACK_IMPORTED_MODULE_1__["default"].post(window.SideMenuData.updateOrderLink, {
      grouparray: onlyGroupsArray,
      surveyid: surveyid
    });
  }
  return {
    updatePjax: updatePjax,
    getSidemenus: getSidemenus,
    getCollapsedmenus: getCollapsedmenus,
    getQuestions: getQuestions,
    collectMenus: collectMenus,
    unlockLockOrganizer: unlockLockOrganizer,
    changeCurrentTab: changeCurrentTab,
    updateQuestionGroupOrder: updateQuestionGroupOrder
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Actions);

/***/ }),

/***/ "./src/AjaxHelper.js":
/*!***************************!*\
  !*** ./src/AjaxHelper.js ***!
  \***************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * AjaxHelper - Vanilla JS replacement for runAjax mixin
 * Provides Promise-based AJAX methods using jQuery
 */
const AjaxHelper = function () {
  'use strict';

  /**
   * Core AJAX request method
   * @param {string} uri - Request URL
   * @param {Object} data - Request data
   * @param {string} method - HTTP method
   * @returns {Promise}
   */
  function _runAjax(uri, data, method) {
    data = data || {};
    method = method || 'get';
    return new Promise(function (resolve, reject) {
      if (typeof $ === 'undefined') {
        reject('JQUERY NOT AVAILABLE!');
        return;
      }
      $.ajax({
        url: uri,
        method: method,
        data: data,
        dataType: 'json',
        success: function (response, status, xhr) {
          resolve({
            success: true,
            data: response,
            transferStatus: status,
            xhr: xhr
          });
        },
        error: function (xhr, status, error) {
          const responseData = xhr.responseJSON || xhr.responseText;
          reject({
            success: false,
            error: error,
            data: responseData,
            transferStatus: status,
            xhr: xhr
          });
        }
      });
    });
  }

  /**
   * POST request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function post(uri, data) {
    return _runAjax(uri, data, 'post');
  }

  /**
   * GET request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function get(uri, data) {
    return _runAjax(uri, data, 'get');
  }

  /**
   * DELETE request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function deleteRequest(uri, data) {
    return _runAjax(uri, data, 'delete');
  }

  /**
   * PUT request
   * @param {string} uri
   * @param {Object} data
   * @returns {Promise}
   */
  function put(uri, data) {
    return _runAjax(uri, data, 'put');
  }
  return {
    post: post,
    get: get,
    delete: deleteRequest,
    put: put
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AjaxHelper);

/***/ }),

/***/ "./src/StateManager.js":
/*!*****************************!*\
  !*** ./src/StateManager.js ***!
  \*****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/**
 * StateManager - Vanilla JS replacement for Vuex store
 * Manages sidebar state with sessionStorage persistence
 *
 * Unified implementation used across admin and global sidepanels
 */
const StateManager = function () {
  'use strict';

  let state = {};
  let storageKey = '';
  let listeners = [];
  let mutations = {};
  let getters = {};

  /**
   * Initialize state with default values
   * @param {Object} config - Configuration object
   * @param {string} config.storagePrefix - Storage key prefix (e.g., 'limesurveyadminsidepanel')
   * @param {string|number} [config.userid] - User ID
   * @param {string|number} [config.surveyid] - Survey ID (optional)
   * @param {Object} config.defaultState - Default state object
   * @param {Object} [config.mutations] - Mutations object (optional)
   * @param {Object} [config.getters] - Getters object (optional)
   */
  function init(config) {
    if (!config || !config.storagePrefix || !config.defaultState) {
      console.error('StateManager.init requires storagePrefix and defaultState');
      return state;
    }

    // Build storage key
    storageKey = config.storagePrefix;
    if (config.userid) {
      storageKey += '_' + config.userid;
    }
    if (config.surveyid) {
      storageKey += '_' + config.surveyid;
    }

    // Set mutations and getters
    mutations = config.mutations || {};
    getters = config.getters || {};

    // Try to load from sessionStorage
    const savedState = loadFromStorage();
    state = Object.assign({}, config.defaultState, savedState);
    return state;
  }

  /**
   * Load state from sessionStorage
   */
  function loadFromStorage() {
    try {
      const saved = sessionStorage.getItem(storageKey);
      if (saved) {
        return JSON.parse(saved);
      }
    } catch (e) {
      console.warn('Failed to load state from sessionStorage:', e);
    }
    return {};
  }

  /**
   * Save state to sessionStorage
   */
  function saveToStorage() {
    try {
      sessionStorage.setItem(storageKey, JSON.stringify(state));
    } catch (e) {
      console.warn('Failed to save state to sessionStorage:', e);
    }
  }

  /**
   * Get current state value
   * @param {string} [key] - State key to retrieve (omit to get entire state)
   * @returns {*} State value or entire state object
   */
  function get(key) {
    if (key) {
      return state[key];
    }
    return state;
  }

  /**
   * Set state value and persist
   * @param {string} key - State key
   * @param {*} value - New value
   */
  function set(key, value) {
    const oldValue = state[key];
    state[key] = value;
    saveToStorage();
    notifyListeners(key, value, oldValue);
  }

  /**
   * Subscribe to state changes
   * @param {Function} callback - Callback function (key, newValue, oldValue)
   * @returns {Function} Unsubscribe function
   */
  function subscribe(callback) {
    listeners.push(callback);
    return function unsubscribe() {
      listeners = listeners.filter(l => l !== callback);
    };
  }

  /**
   * Notify listeners of state change
   * @param {string} key - Changed state key
   * @param {*} newValue - New value
   * @param {*} oldValue - Old value
   */
  function notifyListeners(key, newValue, oldValue) {
    listeners.forEach(function (listener) {
      listener(key, newValue, oldValue);
    });
  }

  /**
   * Commit a mutation
   * @param {string} mutation - Mutation name
   * @param {*} payload - Mutation payload
   */
  function commit(mutation, payload) {
    if (mutations[mutation]) {
      mutations[mutation](payload);
    } else {
      console.warn('Unknown mutation:', mutation);
    }
  }

  /**
   * Get a computed value from getters
   * @param {string} getter - Getter name
   * @returns {*} Computed value
   */
  function getComputed(getter) {
    if (getters[getter]) {
      return getters[getter]();
    }
    console.warn('Unknown getter:', getter);
    return undefined;
  }

  /**
   * Register mutations (can be called after init to add more mutations)
   * @param {Object} newMutations - Mutations to register
   */
  function registerMutations(newMutations) {
    Object.assign(mutations, newMutations);
  }

  /**
   * Register getters (can be called after init to add more getters)
   * @param {Object} newGetters - Getters to register
   */
  function registerGetters(newGetters) {
    Object.assign(getters, newGetters);
  }
  return {
    init: init,
    get: get,
    set: set,
    commit: commit,
    getComputed: getComputed,
    subscribe: subscribe,
    registerMutations: registerMutations,
    registerGetters: registerGetters,
    getState: function () {
      return state;
    }
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (StateManager);

/***/ }),

/***/ "./src/UIHelpers.js":
/*!**************************!*\
  !*** ./src/UIHelpers.js ***!
  \**************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.string.replace.js */ "./node_modules/core-js/modules/es.string.replace.js");
/* harmony import */ var core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_string_replace_js__WEBPACK_IMPORTED_MODULE_1__);


/**
 * UIHelpers - Utility functions for UI operations
 */
const UIHelpers = function () {
  'use strict';

  /**
   * Translate a string using SideMenuData translations
   * @param {string} str - String to translate
   * @returns {string}
   */
  function translate(str) {
    if (window.SideMenuData && window.SideMenuData.translate) {
      return window.SideMenuData.translate[str] || str;
    }
    return str;
  }

  /**
   * Re-initialize tooltips
   */
  function redoTooltips() {
    if (window.LS && window.LS.doToolTip) {
      window.LS.doToolTip();
    }
  }

  /**
   * Convert HTML entities back to characters
   * Uses the same character mapping as the original Vue component
   * @param {string} string
   * @returns {string}
   */
  function reConvertHTML(string) {
    if (!string) return '';

    // HTML entity decode map (subset of commonly used)
    var entityMap = {
      '&#039;': "'",
      '&copy;': '\u00A9',
      '&reg;': '\u00AE',
      '&#36;': '$',
      '&#37;': '%',
      '&#64;': '@',
      '&Agrave;': '\u00C0',
      '&Aacute;': '\u00C1',
      '&Acirc;': '\u00C2',
      '&Atilde;': '\u00C3',
      '&Auml;': '\u00C4',
      '&Aring;': '\u00C5',
      '&AElig;': '\u00C6',
      '&Ccedil;': '\u00C7',
      '&Egrave;': '\u00C8',
      '&Eacute;': '\u00C9',
      '&Ecirc;': '\u00CA',
      '&Euml;': '\u00CB',
      '&Igrave;': '\u00CC',
      '&Iacute;': '\u00CD',
      '&Icirc;': '\u00CE',
      '&Iuml;': '\u00CF',
      '&ETH;': '\u00D0',
      '&Ntilde;': '\u00D1',
      '&Otilde;': '\u00D5',
      '&Ouml;': '\u00D6',
      '&Oslash;': '\u00D8',
      '&Ugrave;': '\u00D9',
      '&Uacute;': '\u00DA',
      '&Ucirc;': '\u00DB',
      '&Uuml;': '\u00DC',
      '&Yacute;': '\u00DD',
      '&THORN;': '\u00DE',
      '&szlig;': '\u00DF',
      '&agrave;': '\u00E0',
      '&aacute;': '\u00E1',
      '&acirc;': '\u00E2',
      '&atilde;': '\u00E3',
      '&auml;': '\u00E4',
      '&aring;': '\u00E5',
      '&aelig;': '\u00E6',
      '&ccedil;': '\u00E7',
      '&egrave;': '\u00E8',
      '&eacute;': '\u00E9',
      '&ecirc;': '\u00EA',
      '&euml;': '\u00EB',
      '&igrave;': '\u00EC',
      '&iacute;': '\u00ED',
      '&icirc;': '\u00EE',
      '&iuml;': '\u00EF',
      '&eth;': '\u00F0',
      '&ntilde;': '\u00F1',
      '&ograve;': '\u00F2',
      '&oacute;': '\u00F3',
      '&ocirc;': '\u00F4',
      '&otilde;': '\u00F5',
      '&ouml;': '\u00F6',
      '&oslash;': '\u00F8',
      '&ugrave;': '\u00F9',
      '&uacute;': '\u00FA',
      '&ucirc;': '\u00FB',
      '&yacute;': '\u00FD',
      '&thorn;': '\u00FE',
      '&yuml;': '\u00FF'
    };
    for (var entity in entityMap) {
      if (entityMap.hasOwnProperty(entity)) {
        string = string.split(entity).join(entityMap[entity]);
      }
    }

    // Also handle numeric entities
    string = string.replace(/&#(\d+);/g, function (match, dec) {
      return String.fromCharCode(dec);
    });
    return string;
  }

  /**
   * Render a menu icon based on type
   * @param {string} iconType
   * @param {string} icon
   * @returns {string} HTML string
   */
  function renderMenuIcon(iconType, icon) {
    if (!icon) return '';
    switch (iconType) {
      case 'fontawesome':
        return '<i class="fa fa-' + icon + '">&nbsp;</i>';
      case 'image':
        return '<img width="32px" src="' + icon + '" />';
      case 'iconclass':
      case 'remix':
        return '<i class="' + icon + '">&nbsp;</i>';
      default:
        return '';
    }
  }

  /**
   * Create a loader widget HTML
   * @param {string} id
   * @param {string} extraClass
   * @returns {string}
   */
  function createLoaderWidget(id, extraClass) {
    id = id || 'loader-' + Math.floor(1000 * Math.random());
    extraClass = extraClass || '';
    return '<div id="' + id + '" class="loader--loaderWidget ls-flex ls-flex-column align-content-center align-items-center" style="min-height: 100%;">' + '<div class="ls-flex align-content-center align-items-center">' + '<div class="loader-adminpanel text-center ' + extraClass + '">' + '<div class="contain-pulse animate-pulse">' + '<div class="square"></div>' + '<div class="square"></div>' + '<div class="square"></div>' + '<div class="square"></div>' + '</div>' + '</div>' + '</div>' + '</div>';
  }

  /**
   * Parse integer or return default value
   * @param {*} val
   * @param {number} defaultVal
   * @returns {number}
   */
  function parseIntOr(val, defaultVal) {
    defaultVal = defaultVal !== undefined ? defaultVal : 999999;
    var intVal = parseInt(val, 10);
    if (isNaN(intVal)) {
      return defaultVal;
    }
    return intVal;
  }

  /**
   * Check if we're in mobile view
   * @returns {boolean}
   */
  function useMobileView() {
    return window.innerWidth < 768;
  }

  /**
   * @param {string} str
   * @returns {string}
   */
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  return {
    translate: translate,
    redoTooltips: redoTooltips,
    reConvertHTML: reConvertHTML,
    renderMenuIcon: renderMenuIcon,
    createLoaderWidget: createLoaderWidget,
    parseIntOr: parseIntOr,
    useMobileView: useMobileView,
    escapeHtml: escapeHtml
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (UIHelpers);

/***/ }),

/***/ "./src/components/QuestionExplorer.js":
/*!********************************************!*\
  !*** ./src/components/QuestionExplorer.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _Actions_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../Actions.js */ "./src/Actions.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");
/**
 * QuestionExplorer - Question groups explorer component
 * Matches original _questionsgroups.vue implementation
 */



const QuestionExplorer = function () {
  'use strict';

  let container = null;
  let onOrderChange = null;

  // Drag and drop state - matching Vue component data()
  let active = [];
  let questiongroupDragging = false;
  let draggedQuestionGroup = null;
  let questionDragging = false;
  let draggedQuestion = null;
  let draggedQuestionsGroup = null;
  let orderChanged = false; // Track if order actually changed during drag

  /**
   * Render the question explorer
   */
  function render(containerEl, loading, orderChangeCallback) {
    container = containerEl;
    onOrderChange = orderChangeCallback;
    if (!container) return;
    active = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questionGroupOpenArray') || [];
    renderExplorer();
  }

  /**
   * Check if group is open
   */
  function isOpen(gid) {
    if (questiongroupDragging === true) return false;
    return LS.ld.indexOf(active, gid) !== -1;
  }

  /**
   * Check if group is active
   */
  function isActive(gid) {
    return gid == _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastQuestionGroupOpen');
  }

  /**
   * Get question group item classes - matching Vue questionGroupItemClasses()
   */
  function questionGroupItemClasses(questiongroup) {
    var classes = '';
    classes += isOpen(questiongroup.gid) ? ' selected ' : ' ';
    classes += isActive(questiongroup.gid) ? ' activated ' : ' ';
    if (draggedQuestionGroup !== null) {
      classes += draggedQuestionGroup.gid === questiongroup.gid ? ' dragged' : ' ';
    }
    return classes;
  }

  /**
   * Get question item classes - matching Vue questionItemClasses()
   */
  function questionItemClasses(question) {
    var classes = '';
    classes += _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastQuestionOpen') === question.qid ? 'selected activated' : 'selected ';
    if (draggedQuestion !== null) {
      classes += draggedQuestion.qid === question.qid ? ' dragged' : ' ';
    }
    return classes;
  }

  /**
   * Render the explorer content - matching Vue template exactly
   */
  function renderExplorer() {
    if (!container) return;
    var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
    var allowOrganizer = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('allowOrganizer') === null ? 1 : _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('allowOrganizer') === 1;
    var surveyIsActive = window.SideMenuData.isActive;
    var createQuestionGroupLink = window.SideMenuData.createQuestionGroupLink;
    var createQuestionLink = window.SideMenuData.createQuestionLink;
    var createQuestionAllowed = questiongroups.length > 0 && createQuestionLink && createQuestionLink.length > 1;
    var createQuestionAllowedClass = createQuestionAllowed ? '' : 'disabled';
    var createQuestionGroupAllowedClass = createQuestionGroupLink && createQuestionGroupLink.length > 1 ? '' : 'disabled';
    var orderedQuestionGroups = LS.ld.orderBy(questiongroups, function (a) {
      return _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].parseIntOr(a.group_order, 999999);
    }, ['asc']);
    var itemWidth = parseInt(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('sidebarwidth')) - 120 + 'px';
    var html = '<div id="questionexplorer" class="ls-flex-column fill ls-ba menu-pane h-100 pt-2">';

    // Toolbar buttons
    html += '<div class="ls-flex-row button-sub-bar mb-2">';
    html += '<div class="scoped-toolbuttons-right me-2">';
    html += '<button class="btn btn-sm btn-outline-secondary toggle-organizer-btn" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate(allowOrganizer ? 'lockOrganizerTitle' : 'unlockOrganizerTitle') + '">';
    html += '<i class="' + (allowOrganizer ? 'ri-lock-unlock-fill' : 'ri-lock-fill') + '"></i>';
    html += '</button>';
    html += '<button class="btn btn-sm btn-outline-secondary me-2 collapse-all-btn" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('collapseAll') + '">';
    html += '<i class="ri-link-unlink"></i>';
    html += '</button>';
    html += '</div>';
    html += '</div>';

    // Create buttons
    html += '<div class="ls-flex-row wrap align-content-center align-items-center button-sub-bar">';
    html += '<div class="scoped-toolbuttons-left mb-2 d-flex align-items-center">';
    var createQuestionTooltip = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate(createQuestionAllowed ? '' : 'deactivateSurvey');
    html += '<div class="create-question px-3" data-bs-toggle="tooltip" data-bs-placement="top" title="' + createQuestionTooltip + '">';
    html += '<a id="adminsidepanel__sidebar--selectorCreateQuestion" href="' + createFullQuestionLink(createQuestionLink) + '" class="btn btn-primary pjax ' + createQuestionAllowedClass + '">';
    html += '<i class="ri-add-circle-fill"></i>&nbsp;' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('createQuestion');
    html += '</a>';
    html += '</div>';
    html += '<div data-bs-toggle="tooltip" data-bs-placement="top" title="' + createQuestionTooltip + '">';
    html += '<a id="adminsidepanel__sidebar--selectorCreateQuestionGroup" href="' + createQuestionGroupLink + '" class="btn btn-secondary pjax ' + createQuestionGroupAllowedClass + '">';
    html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].translate('createPage');
    html += '</a>';
    html += '</div>';
    html += '</div>';
    html += '</div>';

    // Question groups list
    html += '<div class="ls-flex-row ls-space padding all-0">';
    html += '<ul class="list-group col-12 questiongroup-list-group">';
    orderedQuestionGroups.forEach(function (questiongroup) {
      html += renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, itemWidth);
    });
    html += '</ul>';
    html += '</div>';
    html += '</div>';
    container.innerHTML = html;
    bindEvents();
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].redoTooltips();
  }
  function createFullQuestionLink(baseLink) {
    if (!baseLink) return '#';
    if (LS.reparsedParameters && LS.reparsedParameters().combined && LS.reparsedParameters().combined.gid) {
      return baseLink + '&gid=' + LS.reparsedParameters().combined.gid;
    }
    return baseLink;
  }

  /**
   * Render question group - matching Vue template
   */
  function renderQuestionGroup(questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
    var classes = 'list-group-item ls-flex-column' + questionGroupItemClasses(questiongroup);
    var isGroupOpen = isOpen(questiongroup.gid);
    var groupActivated = isActive(questiongroup.gid);
    var html = '<li class="' + classes + '" data-gid="' + questiongroup.gid + '">';

    // Question group header
    html += '<div class="q-group d-flex nowrap ls-space padding right-5 bottom-5 bg-white ms-2 p-2" data-gid="' + questiongroup.gid + '">';

    // Drag handle
    html += '<div class="bigIcons dragPointer me-1 questiongroup-drag-handle ' + (allowOrganizer ? '' : 'disabled') + '" ';
    html += (allowOrganizer ? 'draggable="true"' : '') + ' data-gid="' + questiongroup.gid + '">';
    html += '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">';
    html += '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>';
    html += '</svg>';
    html += '</div>';

    // Expand/collapse toggle
    var rotateStyle = isGroupOpen ? 'transform: rotate(90deg)' : 'transform: rotate(0deg)';
    html += '<div class="cursor-pointer me-1 toggle-questiongroup" data-gid="' + questiongroup.gid + '" style="' + rotateStyle + '">';
    html += '<i class="ri-arrow-right-s-fill"></i>';
    html += '</div>';

    // Question group name
    html += '<div class="w-100 position-relative">';
    html += '<div class="cursor-pointer">';
    html += '<a class="d-flex pjax" href="' + questiongroup.link + '">';
    html += '<span class="question_text_ellipsize" style="max-width: ' + itemWidth + '">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(questiongroup.group_name) + '</span>';
    html += '</a>';
    html += '</div>';

    // Dropdown and badge
    html += '<div class="position-absolute top-0 d-flex align-items-center" style="right:5px">';
    html += '<div class="toggle-questiongroup" data-gid="' + questiongroup.gid + '">';
    html += '<span class="badge reverse-color ls-space margin right-5">' + (questiongroup.questions ? questiongroup.questions.length : 0) + '</span>';
    html += '</div>';

    // Dropdown menu - always render, visibility controlled by hover class
    if (questiongroup.groupDropdown) {
      var dropdownStyle = groupActivated ? '' : ' style="display:none"';
      html += '<div class="dropdown questiongroup-dropdown' + (groupActivated ? ' active' : '') + '"' + dropdownStyle + '>';
      html += '<div class="ls-questiongroup-tools cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">';
      html += '<i class="ri-more-fill"></i>';
      html += '</div>';
      html += '<ul class="dropdown-menu">';
      for (var key in questiongroup.groupDropdown) {
        if (!questiongroup.groupDropdown.hasOwnProperty(key)) continue;
        var value = questiongroup.groupDropdown[key];
        if (key !== 'delete') {
          html += '<li>';
          html += '<a class="dropdown-item" id="' + (value.id || '') + '" href="' + value.url + '">';
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        } else {
          html += '<li class="' + (value.disabled ? 'disabled' : '') + '">';
          if (!value.disabled) {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-btnclass="btn-danger" data-title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataTitle || '') + '" data-btntext="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataBtnText || '') + '" data-onclick="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataOnclick || '') + '" data-message="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataMessage || '') + '">';
          } else {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.title || '') + '">';
          }
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        }
      }
      html += '</ul>';
      html += '</div>';
    }
    html += '</div>';
    html += '</div>';
    html += '</div>';

    // Questions list (if open) - matching Vue transition
    if (isGroupOpen && questiongroup.questions) {
      html += renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, itemWidth);
    }
    html += '</li>';
    return html;
  }

  /**
   * Render questions list
   */
  function renderQuestionsList(questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
    var orderedQuestions = LS.ld.orderBy(questiongroup.questions, function (a) {
      return _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].parseIntOr(a.question_order, 999999);
    }, ['asc']);
    var html = '<ul class="list-group background-muted padding-left question-question-list" style="padding-right:15px">';
    orderedQuestions.forEach(function (question) {
      html += renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, itemWidth);
    });
    html += '</ul>';
    return html;
  }

  /**
   * Render single question - matching Vue template exactly
   */
  function renderQuestion(question, questiongroup, allowOrganizer, surveyIsActive, itemWidth) {
    var classes = 'list-group-item question-question-list-item ls-flex-row align-itmes-flex-start ' + questionItemClasses(question);
    var itemActivated = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('lastQuestionOpen') === question.qid;
    // Always show dropdown HTML, use CSS/JS hover to control visibility
    var showDropdown = true;
    var questionHasCondition = question.relevance !== '1';
    var html = '<li class="' + classes + '" data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '" data-is-hidden="' + question.hidden + '" data-questiontype="' + question.type + '" data-has-condition="' + questionHasCondition + '" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(question.question_flat) + '" data-bs-toggle="tooltip">';

    // Drag handle (only if survey not active)
    if (!surveyIsActive) {
      html += '<div class="margin-right bigIcons dragPointer question-question-list-item-drag question-drag-handle ' + (allowOrganizer ? '' : 'disabled') + '" ';
      html += (allowOrganizer ? 'draggable="true"' : '') + ' data-qid="' + question.qid + '" data-gid="' + questiongroup.gid + '">';
      html += '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">';
      html += '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>';
      html += '</svg>';
      html += '</div>';
    }

    // Question link
    html += '<a href="' + question.link + '" class="col-9 pjax question-question-list-item-link display-as-container question-link" data-qid="' + question.qid + '" data-gid="' + question.gid + '">';
    html += '<span class="question_text_ellipsize ' + (question.hidden ? 'question-hidden' : '') + '" style="width: ' + itemWidth + '">';
    html += '[' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(question.title) + '] &rsaquo; ' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(question.question_flat);
    html += '</span>';
    html += '</a>';

    // Question dropdown - always render, visibility controlled by hover class
    if (question.questionDropdown) {
      var dropdownStyle = itemActivated ? 'right:10px' : 'right:10px;display:none';
      html += '<div class="dropdown question-dropdown position-absolute' + (itemActivated ? ' active' : '') + '" style="' + dropdownStyle + '">';
      html += '<div class="ls-question-tools ms-auto position-relative cursor-pointer" data-bs-toggle="dropdown" aria-expanded="false">';
      html += '<i class="ri-more-fill"></i>';
      html += '</div>';
      html += '<ul class="dropdown-menu">';
      for (var key in question.questionDropdown) {
        if (!question.questionDropdown.hasOwnProperty(key)) continue;
        var value = question.questionDropdown[key];
        if (key !== 'delete' && !(key === 'language' && Array.isArray(value))) {
          var isDisabled = key === 'editDefault' && value.active === 0;
          html += '<li>';
          html += '<a class="dropdown-item ' + (isDisabled ? 'disabled' : '') + '" id="' + (value.id || '') + '" href="' + (isDisabled ? '#' : value.url) + '">';
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        } else if (key === 'delete') {
          html += '<li class="' + (value.disabled ? 'disabled' : '') + '">';
          if (!value.disabled) {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#confirmation-modal" data-btnclass="btn-danger" data-title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataTitle || '') + '" data-btntext="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataBtnText || '') + '" data-onclick="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataOnclick || '') + '" data-message="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.dataMessage || '') + '">';
          } else {
            html += '<a href="#" onclick="return false;" class="dropdown-item" data-bs-toggle="tooltip" data-bs-placement="bottom" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(value.title || '') + '">';
          }
          html += '<span class="' + (value.icon || '') + '"></span> ' + value.label;
          html += '</a>';
          html += '</li>';
        } else if (key === 'language' && Array.isArray(value)) {
          html += '<li role="separator" class="dropdown-divider"></li>';
          html += '<li class="dropdown-header">Survey logic file</li>';
          value.forEach(function (language) {
            html += '<li>';
            html += '<a class="dropdown-item" id="' + (language.id || '') + '" href="' + language.url + '">';
            html += '<span class="' + (language.icon || '') + '"></span> ' + language.label;
            html += '</a>';
            html += '</li>';
          });
        }
      }
      html += '</ul>';
      html += '</div>';
    }
    html += '</li>';
    return html;
  }

  /**
   * Add to active array
   */
  function addActive(questionGroupId) {
    if (!isOpen(questionGroupId)) {
      active.push(questionGroupId);
    }
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('questionGroupOpenArray', active);
  }

  /**
   * Toggle question group - matching Vue toggleQuestionGroup()
   */
  function toggleQuestionGroup(questiongroup) {
    if (!isOpen(questiongroup.gid)) {
      addActive(questiongroup.gid);
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastQuestionGroupOpen', questiongroup);
    } else {
      var newActive = active.filter(function (gid) {
        return gid !== questiongroup.gid;
      });
      active = newActive.slice();
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('questionGroupOpenArray', active);
    }
    renderExplorer();
  }

  /**
   * Open question - matching Vue openQuestion()
   */
  function openQuestion(question) {
    addActive(question.gid);
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('lastQuestionOpen', question);
    $(document).trigger('pjax:load', {
      url: question.link
    });
  }

  /**
   * Collapse all
   */
  function collapseAll() {
    active = [];
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('questionGroupOpenArray', active);
    renderExplorer();
  }

  /**
   * Bind events
   */
  function bindEvents() {
    if (!container) return;
    var $container = $(container);
    $container.off('.qe');

    // Toggle organizer
    $container.on('click.qe', '.toggle-organizer-btn', function (e) {
      e.preventDefault();

      // Update server and re-render
      _Actions_js__WEBPACK_IMPORTED_MODULE_1__["default"].unlockLockOrganizer().then(function () {
        // Toggle the state locally
        renderExplorer();
      });
    });

    // Collapse all
    $container.on('click.qe', '.collapse-all-btn', function (e) {
      e.preventDefault();
      collapseAll();
    });

    // Toggle question group
    $container.on('click.qe', '.toggle-questiongroup', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var gid = $(this).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var group = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (group) {
        toggleQuestionGroup(group);
      }
    });

    // Question link click - matching Vue @click.stop.prevent="openQuestion(question)"
    $container.on('click.qe', '.question-link', function (e) {
      e.preventDefault();
      e.stopPropagation();
      var qid = $(this).data('qid');
      var gid = $(this).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var group = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (group && group.questions) {
        var question = group.questions.find(function (q) {
          return q.qid === qid;
        });
        if (question) {
          openQuestion(question);
        }
      }
    });

    // Hover events for dropdown visibility - matching Vue mouseover/mouseleave behavior
    // Show dropdown on question group hover
    $container.on('mouseover.qe', '.q-group[data-gid]', function (e) {
      $(this).find('.questiongroup-dropdown:not(.active)').show();
    });
    $container.on('mouseleave.qe', '.q-group[data-gid]', function (e) {
      $(this).find('.questiongroup-dropdown:not(.active)').hide();
    });

    // Show dropdown on question hover - use mouseover to match Vue behavior
    $container.on('mouseover.qe', '.question-question-list-item', function (e) {
      $(this).find('.question-dropdown:not(.active)').show();
    });
    $container.on('mouseleave.qe', '.question-question-list-item', function (e) {
      $(this).find('.question-dropdown:not(.active)').hide();
    });

    // Drag events
    bindDragEvents($container);
  }

  /**
   * Bind drag events - matching Vue drag methods exactly
   * IMPORTANT: Avoid calling renderExplorer() during active drag to maintain smooth operation
   */
  function bindDragEvents($container) {
    // Question group drag start - matching startDraggingGroup
    $container.on('dragstart.qe', '.questiongroup-drag-handle[draggable="true"]', function (e) {
      var gid = $(this).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      draggedQuestionGroup = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      questiongroupDragging = true;
      orderChanged = false; // Reset flag at start of drag
      e.originalEvent.dataTransfer.setData('text/plain', 'node');
      // Add dragged class directly without re-rendering
      $(this).closest('.list-group-item').addClass('dragged');
    });

    // Question group drag end - matching endDraggingGroup
    $container.on('dragend.qe', '.questiongroup-drag-handle', function () {
      if (draggedQuestionGroup !== null) {
        draggedQuestionGroup = null;
        questiongroupDragging = false;
        // Only trigger order update if order actually changed
        if (orderChanged && onOrderChange) {
          onOrderChange();
        }
        orderChanged = false; // Reset flag
        renderExplorer();
      }
    });

    // Question group dragenter - matching dragoverQuestiongroup
    $container.on('dragenter.qe', '.list-group-item[data-gid]', function (e) {
      e.preventDefault();
      var gid = $(this).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var questiongroupObject = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (questiongroupDragging && draggedQuestionGroup && questiongroupObject) {
        var targetPosition = parseInt(questiongroupObject.group_order);
        var currentPosition = parseInt(draggedQuestionGroup.group_order);
        if (Math.abs(targetPosition - currentPosition) === 1) {
          questiongroupObject.group_order = currentPosition;
          draggedQuestionGroup.group_order = targetPosition;
          _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateQuestiongroups', questiongroups);
          orderChanged = true; // Mark that order has changed
          // Don't re-render during drag - wait for dragend
        }
      } else if (questionDragging && draggedQuestion && questiongroupObject) {
        if (window.SideMenuData.isActive) return;
        addActive(questiongroupObject.gid);
        if (draggedQuestion.gid !== questiongroupObject.gid) {
          var removedFromInitial = LS.ld.remove(draggedQuestionsGroup.questions, function (q) {
            return q.qid === draggedQuestion.qid;
          });
          if (removedFromInitial.length > 0) {
            draggedQuestion.question_order = null;
            questiongroupObject.questions.push(draggedQuestion);
            draggedQuestion.gid = questiongroupObject.gid;
            if (questiongroupObject.group_order > draggedQuestionsGroup.group_order) {
              draggedQuestion.question_order = 0;
              LS.ld.each(questiongroupObject.questions, function (q) {
                q.question_order = parseInt(q.question_order) + 1;
              });
            } else {
              draggedQuestion.question_order = draggedQuestionsGroup.questions.length + 1;
            }
            draggedQuestionsGroup = questiongroupObject;
            _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateQuestiongroups', questiongroups);
            orderChanged = true; // Mark that order has changed
            // Don't re-render during drag - wait for dragend
          }
        }
      }
    });

    // Question drag start - matching startDraggingQuestion
    $container.on('dragstart.qe', '.question-drag-handle[draggable="true"]', function (e) {
      var qid = $(this).data('qid');
      var gid = $(this).data('gid');
      var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
      var group = questiongroups.find(function (g) {
        return g.gid === gid;
      });
      if (group && group.questions) {
        draggedQuestion = group.questions.find(function (q) {
          return q.qid === qid;
        });
        draggedQuestionsGroup = group;
        questionDragging = true;
        orderChanged = false; // Reset flag at start of drag
        e.originalEvent.dataTransfer.setData('application/node', 'node');
        // Add dragged class directly without re-rendering
        $(this).closest('.question-question-list-item').addClass('dragged');
      }
    });

    // Question drag end - matching endDraggingQuestion
    $container.on('dragend.qe', '.question-drag-handle', function () {
      if (questionDragging) {
        questionDragging = false;
        draggedQuestion = null;
        draggedQuestionsGroup = null;
        // Only trigger order update if order actually changed
        if (orderChanged && onOrderChange) {
          onOrderChange();
        }
        orderChanged = false; // Reset flag
        renderExplorer();
      }
    });

    // Question dragenter - matching dragoverQuestion
    $container.on('dragenter.qe', '.question-question-list-item', function (e) {
      e.preventDefault();
      var qid = $(this).data('qid');
      var gid = $(this).data('gid');
      if (questionDragging && draggedQuestion) {
        if (window.SideMenuData.isActive && draggedQuestion.gid !== gid) return;
        var questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].get('questiongroups') || [];
        var group = questiongroups.find(function (g) {
          return g.gid === gid;
        });
        if (group && group.questions) {
          var questionObject = group.questions.find(function (q) {
            return q.qid === qid;
          });
          if (questionObject && questionObject.qid !== draggedQuestion.qid) {
            var orderSwap = questionObject.question_order;
            questionObject.question_order = draggedQuestion.question_order;
            draggedQuestion.question_order = orderSwap;
            _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateQuestiongroups', questiongroups);
            orderChanged = true; // Mark that order has changed
            // Don't re-render during drag - wait for dragend
          }
        }
      }
    });

    // Allow drop
    $container.on('dragover.qe', '.list-group-item, .question-question-list-item', function (e) {
      e.preventDefault();
    });
  }
  return {
    render: render
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (QuestionExplorer);

/***/ }),

/***/ "./src/components/QuickMenu.js":
/*!*************************************!*\
  !*** ./src/components/QuickMenu.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "./node_modules/core-js/modules/web.dom-collections.iterator.js");
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");

/**
 * QuickMenu - Collapsed menu component (vanilla JS)
 * Replaces _quickmenu.vue
 */


const QuickMenu = function () {
  'use strict';

  let container = null;
  let isLoading = true;

  /**
   * Render the quick menu
   * @param {HTMLElement} containerEl
   * @param {boolean} loading
   */
  function render(containerEl, loading) {
    container = containerEl;
    if (!container) return;

    // Menus are loaded from SideMenuData.basemenus in Sidebar.init()
    // Don't make extra AJAX calls - just render what's in state
    isLoading = false;
    renderMenu();
  }

  /**
   * Render the menu content
   */
  function renderMenu() {
    if (!container) return;
    const collapsedmenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].get('collapsedmenus') || [];

    // Sort menus by ordering
    const sortedMenus = LS.ld.orderBy(collapsedmenus, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
    let html = '<div class="ls-flex-column fill">';
    if (isLoading) {
      html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].createLoaderWidget('quickmenuLoadingIcon', 'loader-quickmenu');
    } else {
      sortedMenus.forEach(function (menu) {
        html += '<div class="ls-space margin top-10" title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(menu.title) + '">';
        html += '<div class="btn-group-vertical ls-space padding right-10">';
        const sortedEntries = sortMenuEntries(menu.entries);
        sortedEntries.forEach(function (menuItem) {
          html += renderMenuItem(menuItem);
        });
        html += '</div>';
        html += '</div>';
      });
    }
    html += '</div>';
    container.innerHTML = html;
    bindEvents();
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].redoTooltips();
  }

  /**
   * Sort menu entries by ordering
   * @param {Array} entries
   * @returns {Array}
   */
  function sortMenuEntries(entries) {
    return LS.ld.orderBy(entries, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
  }

  /**
   * Render a single menu item
   * @param {Object} menuItem
   * @returns {string}
   */
  function renderMenuItem(menuItem) {
    const classes = compileEntryClasses(menuItem);
    const tooltip = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].reConvertHTML(menuItem.menu_description);
    const target = menuItem.link_external ? '_blank' : '_self';
    let html = '<a href="' + menuItem.link + '"' + ' title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(tooltip) + '"' + ' target="' + target + '"' + ' data-bs-toggle="tooltip"' + ' class="btn ' + classes + '"' + ' data-menu-item-id="' + menuItem.id + '">';

    // Render icon based on type
    html += renderIcon(menuItem);
    html += '</a>';
    return html;
  }

  /**
   * Render icon based on type
   * @param {Object} menuItem
   * @returns {string}
   */
  function renderIcon(menuItem) {
    const iconType = menuItem.menu_icon_type;
    const icon = menuItem.menu_icon;
    switch (iconType) {
      case 'fontawesome':
        return '<i class="quickmenuIcon fa fa-' + icon + '"></i>';
      case 'image':
        return '<img width="32px" src="' + icon + '" />';
      case 'iconclass':
      case 'remix':
        return '<i class="quickmenuIcon ' + icon + '"></i>';
      default:
        return '';
    }
  }

  /**
   * Compile CSS classes for menu entry
   * @param {Object} menuItem
   * @returns {string}
   */
  function compileEntryClasses(menuItem) {
    let classes = '';
    if (_StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].get('lastMenuItemOpen') === menuItem.id) {
      classes += ' btn-primary ';
    } else {
      classes += ' btn-outline-secondary ';
    }
    if (!menuItem.link_external) {
      classes += ' pjax ';
    }
    return classes;
  }

  /**
   * Bind event handlers
   */
  function bindEvents() {
    if (!container) return;

    // Menu item click
    $(container).off('click', '.btn').on('click', '.btn', function () {
      const menuItemId = $(this).data('menu-item-id');

      // Update state
      _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('lastMenuItemOpen', {
        id: menuItemId,
        menu_id: null
      });

      // Re-render to update selected state
      renderMenu();
    });
  }
  return {
    render: render
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (QuickMenu);

/***/ }),

/***/ "./src/components/SideMenu.js":
/*!************************************!*\
  !*** ./src/components/SideMenu.js ***!
  \************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "./node_modules/core-js/modules/web.dom-collections.iterator.js");
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");

/**
 * SideMenu - Side menu component (vanilla JS)
 * Replaces _sidemenu.vue and _submenu.vue
 */


const SideMenu = function () {
  'use strict';

  let container = null;
  let isLoading = true;

  /**
   * Render the side menu
   * @param {HTMLElement} containerEl
   * @param {boolean} loading
   */
  function render(containerEl, loading) {
    container = containerEl;
    if (!container) return;

    // Menus are loaded from SideMenuData.basemenus in Sidebar.init()
    // Don't make extra AJAX calls - just render what's in state
    isLoading = false;
    renderMenu();
  }

  /**
   * Render the menu content
   */
  function renderMenu() {
    if (!container) return;
    const sidemenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].get('sidemenus') || [];

    // Sort menus by ordering
    const sortedMenus = LS.ld.orderBy(sidemenus, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
    let html = '<div class="ls-flex-column menu-pane overflow-enabled ls-space all-0 py-4 bg-white">';
    if (isLoading) {
      html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].createLoaderWidget('sidemenuLoaderWidget', '');
    } else if (sortedMenus.length >= 2) {
      // First menu (usually main settings)
      html += '<div title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(sortedMenus[0].title) + '" id="' + sortedMenus[0].id + '" class="ls-flex-row wrap ls-space padding all-0">';
      html += renderSubmenu(sortedMenus[0]);
      html += '</div>';

      // Second menu (with label)
      html += '<div title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(sortedMenus[1].title) + '" id="' + sortedMenus[1].id + '" class="ls-flex-row wrap ls-space padding all-0">';
      html += '<label class="menu-label mt-3 p-2 ls-survey-menu-item">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(sortedMenus[1].title) + '</label>';
      html += renderSubmenu(sortedMenus[1]);
      html += '</div>';
    }
    html += '</div>';
    container.innerHTML = html;
    bindEvents();
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].redoTooltips();
  }

  /**
   * Render a submenu
   * @param {Object} menu
   * @returns {string}
   */
  function renderSubmenu(menu) {
    if (!menu || !menu.entries) return '';
    const sortedEntries = LS.ld.orderBy(menu.entries, function (a) {
      return parseInt(a.ordering || 999999);
    }, ['asc']);
    let html = '<ul class="list-group subpanel col-12 level-' + (menu.level || 0) + '">';
    sortedEntries.forEach(function (menuItem) {
      const linkClass = getLinkClass(menuItem);
      const href = menuItem.disabled ? '#' : menuItem.link;
      const target = menuItem.link_external === true ? '_blank' : '';
      const tooltip = menuItem.disabled ? menuItem.disabled_tooltip : _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].reConvertHTML(menuItem.menu_description);
      html += '<a href="' + href + '"' + (target ? ' target="' + target + '"' : '') + ' id="sidemenu_' + menuItem.name + '"' + ' class="list-group-item w-100 ' + linkClass + '"' + ' data-menu-item-id="' + menuItem.id + '"' + ' data-menu-id="' + menuItem.menu_id + '">';
      html += '<div class="d-flex ' + (menuItem.menu_class || '') + '"' + ' title="' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].escapeHtml(tooltip) + '"' + ' data-bs-toggle="tooltip">';
      html += '<div class="ls-space padding all-0 me-auto wrapper">';
      html += _UIHelpers_js__WEBPACK_IMPORTED_MODULE_2__["default"].renderMenuIcon(menuItem.menu_icon_type, menuItem.menu_icon);
      html += '<span class="title">' + (menuItem.menu_title || '') + '</span>';
      if (menuItem.link_external === true) {
        html += '<i class="ri-external-link-fill">&nbsp;</i>';
      }
      html += '</div>';
      html += '</div>';
      html += '</a>';
    });
    html += '</ul>';
    return html;
  }

  /**
   * Get CSS classes for a menu link
   * @param {Object} menuItem
   * @returns {string}
   */
  function getLinkClass(menuItem) {
    let classes = 'nowrap ';
    classes += menuItem.pjax ? 'pjax ' : ' ';
    classes += _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].get('lastMenuItemOpen') === menuItem.id ? 'selected ' : ' ';
    classes += menuItem.menu_icon ? '' : 'ls-survey-menu-item';
    if (menuItem.disabled) {
      classes += ' disabled';
    }
    return classes;
  }

  /**
   * Bind event handlers
   */
  function bindEvents() {
    if (!container) return;

    // Menu item click
    $(container).off('click', '.list-group-item').on('click', '.list-group-item', function (e) {
      const $this = $(this);
      const menuItemId = $this.data('menu-item-id');
      const menuId = $this.data('menu-id');
      if ($this.hasClass('disabled')) {
        e.preventDefault();
        return false;
      }

      // Update state
      _StateManager_js__WEBPACK_IMPORTED_MODULE_1__["default"].commit('lastMenuItemOpen', {
        id: menuItemId,
        menu_id: menuId
      });

      // Re-render to update selected state
      renderMenu();

      // Allow default link behavior (pjax will handle it)
    });
  }
  return {
    render: render
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (SideMenu);

/***/ }),

/***/ "./src/components/Sidebar.js":
/*!***********************************!*\
  !*** ./src/components/Sidebar.js ***!
  \***********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var core_js_modules_es_promise_finally_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! core-js/modules/es.promise.finally.js */ "./node_modules/core-js/modules/es.promise.finally.js");
/* harmony import */ var core_js_modules_es_promise_finally_js__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_promise_finally_js__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var core_js_modules_es_regexp_constructor_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! core-js/modules/es.regexp.constructor.js */ "./node_modules/core-js/modules/es.regexp.constructor.js");
/* harmony import */ var core_js_modules_es_regexp_constructor_js__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_regexp_constructor_js__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! core-js/modules/es.regexp.exec.js */ "./node_modules/core-js/modules/es.regexp.exec.js");
/* harmony import */ var core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_2___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_es_regexp_exec_js__WEBPACK_IMPORTED_MODULE_2__);
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! core-js/modules/web.dom-collections.iterator.js */ "./node_modules/core-js/modules/web.dom-collections.iterator.js");
/* harmony import */ var core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_3___default = /*#__PURE__*/__webpack_require__.n(core_js_modules_web_dom_collections_iterator_js__WEBPACK_IMPORTED_MODULE_3__);
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _Actions_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../Actions.js */ "./src/Actions.js");
/* harmony import */ var _UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../UIHelpers.js */ "./src/UIHelpers.js");
/* harmony import */ var _SideMenu_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./SideMenu.js */ "./src/components/SideMenu.js");
/* harmony import */ var _QuickMenu_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./QuickMenu.js */ "./src/components/QuickMenu.js");
/* harmony import */ var _QuestionExplorer_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./QuestionExplorer.js */ "./src/components/QuestionExplorer.js");




/**
 * Sidebar - Main sidebar component (vanilla JS)
 * Replaces sidebar.vue
 */






const Sidebar = function () {
  'use strict';

  let container = null;
  let sideBarWidth = '315';
  let isMouseDown = false;
  let isMouseDownTimeOut = null;
  let smallScreenHidden = false;
  let showLoader = false;
  let loading = true;

  /**
   * Initialize the sidebar
   * @param {HTMLElement} containerEl
   */
  function init(containerEl) {
    container = containerEl;

    // Set initial collapse state for mobile
    if (window.innerWidth < 768) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeIsCollapsed', false);
    }

    // Set survey active state
    _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('setSurveyActiveState', parseInt(window.SideMenuData.isActive) === 1);

    // Initialize sidebar width (always as a number)
    if (_StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed')) {
      sideBarWidth = 98;
    } else {
      const savedWidth = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('sidebarwidth');
      sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
    }

    // Subscribe to state changes to keep sideBarWidth in sync
    _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].subscribe(function (key, newValue, oldValue) {
      if (key === 'sidebarwidth' && !_StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed')) {
        // Ensure we store as a number
        sideBarWidth = typeof newValue === 'string' ? parseInt(newValue) : newValue;
        // Update the DOM directly for smooth resize
        const sidebar = document.getElementById('sidebar');
        if (sidebar && !isMouseDown) {
          sidebar.style.width = sideBarWidth + 'px';
        }
      } else if (key === 'isCollapsed') {
        if (newValue) {
          sideBarWidth = 98;
        } else {
          const savedWidth = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('sidebarwidth');
          sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
        }
        render();
      }
    });

    // Process base menus from SideMenuData
    if (window.SideMenuData && window.SideMenuData.basemenus) {
      LS.ld.each(window.SideMenuData.basemenus, setBaseMenuPosition);
    }
    render();
    bindEvents();
    calculateHeight();

    // Initial data load - check if menus are already loaded from basemenus
    const sidemenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('sidemenus');
    if (sidemenus && sidemenus.length > 0) {
      // Menus already loaded from basemenus, no need to show loading
      loading = false;
    } else {
      loading = true;
    }
    renderContent();

    // Trigger sidebar mounted event
    $(document).trigger('sidebar:mounted');
  }

  /**
   * Set base menu position
   */
  function setBaseMenuPosition(entries, position) {
    const orderedEntries = LS.ld.orderBy(entries, function (a) {
      return parseInt(a.order || 999999);
    }, ['desc']);
    switch (position) {
      case 'side':
        _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('updateSidemenus', orderedEntries);
        break;
      case 'collapsed':
        _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('updateCollapsedmenus', orderedEntries);
        break;
    }
  }

  /**
   * Calculate sidebar height based on viewport
   */
  function calculateHeight() {
    const height = $('#in_survey_common').height();
    if (height) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeSideBarHeight', height);
    }
  }

  /**
   * Get current sidebar width (returns numeric value without 'px')
   */
  function getSideBarWidth() {
    const width = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed') ? 98 : sideBarWidth;
    // Ensure we always return a number by parsing if needed
    return typeof width === 'string' ? parseInt(width) : width;
  }

  /**
   * Calculate sidebar menu height
   */
  function calculateSideBarMenuHeight() {
    const currentSideBar = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('sideBarHeight');
    return LS.ld.min([currentSideBar, Math.floor(screen.height * 2)]) + 'px';
  }

  /**
   * Toggle collapse state
   */
  function toggleCollapse() {
    const isCollapsed = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('isCollapsed');
    _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeIsCollapsed', !isCollapsed);
    if (_StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed')) {
      sideBarWidth = 98;
    } else {
      const savedWidth = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('sidebarwidth');
      sideBarWidth = typeof savedWidth === 'string' ? parseInt(savedWidth) : savedWidth;
    }
    render();
  }

  /**
   * Toggle small screen hidden state
   */
  function toggleSmallScreenHide() {
    smallScreenHidden = !smallScreenHidden;
    render();
  }

  /**
   * Change current tab
   */
  function changeCurrentTab(tab) {
    // Normalize tab name - 'structure' is alias for 'questiontree'
    if (tab === 'structure') {
      tab = 'questiontree';
    }
    // Only allow valid tab values
    if (tab !== 'settings' && tab !== 'questiontree') {
      tab = 'settings';
    }
    _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeCurrentTab', tab);
    render();
  }

  /**
   * Handle mouse down for resize
   */
  function handleMouseDown(e) {
    if (_UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].useMobileView()) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeIsCollapsed', false);
      smallScreenHidden = !smallScreenHidden;
      render();
      return;
    }
    isMouseDown = !_StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed');
    $('#sidebar').removeClass('transition-animate-width');
    $('#pjax-content').removeClass('transition-animate-width');
  }

  /**
   * Handle mouse up for resize
   */
  function handleMouseUp(e) {
    if (isMouseDown) {
      isMouseDown = false;
      const widthNum = typeof sideBarWidth === 'string' ? parseInt(sideBarWidth) : sideBarWidth;
      if (widthNum < 250 && !_StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed')) {
        toggleCollapse();
        _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeSidebarwidth', 340);
      } else {
        _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeSidebarwidth', widthNum);
      }
      $('#sidebar').addClass('transition-animate-width');
      $('#pjax-content').removeClass('transition-animate-width');
    }
  }

  /**
   * Handle mouse leave for resize
   */
  function handleMouseLeave(e) {
    if (isMouseDown) {
      isMouseDownTimeOut = setTimeout(function () {
        handleMouseUp(e);
      }, 1000);
    }
  }

  /**
   * Handle mouse move for resize
   */
  function handleMouseMove(e) {
    if (!isMouseDown) return;
    const isRTL = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isRTL');

    // Prevent emitting unwanted value on dragend
    if (e.screenX === 0 && e.screenY === 0) {
      return;
    }
    if (isRTL) {
      if (window.innerWidth - e.clientX > screen.width / 2) {
        _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('maxSideBarWidth', true);
        return;
      }
      sideBarWidth = window.innerWidth - e.pageX - 8;
    } else {
      if (e.clientX > screen.width / 2) {
        _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('maxSideBarWidth', true);
        return;
      }
      sideBarWidth = e.pageX - 4;
    }
    _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('changeSidebarwidth', sideBarWidth);
    _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('maxSideBarWidth', false);
    window.clearTimeout(isMouseDownTimeOut);
    isMouseDownTimeOut = null;

    // Update sidebar width in real-time (sideBarWidth is a number, add px)
    $('#sidebar').css('width', sideBarWidth + 'px');
  }

  /**
   * Control active link highlighting
   */
  function controlActiveLink() {
    const currentUrl = window.location.href;
    const sidemenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('sidemenus') || [];
    const collapsedmenus = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('collapsedmenus') || [];
    const questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('questiongroups') || [];

    // Check for corresponding menuItem
    let lastMenuItemObject = false;
    LS.ld.each(sidemenus, function (itm) {
      LS.ld.each(itm.entries, function (itmm) {
        if (LS.ld.endsWith(currentUrl, itmm.link)) {
          lastMenuItemObject = itmm;
        }
      });
    });

    // Check for quickmenu menuLinks
    let lastQuickMenuItemObject = false;
    LS.ld.each(collapsedmenus, function (itm) {
      LS.ld.each(itm.entries, function (itmm) {
        if (LS.ld.endsWith(currentUrl, itmm.link)) {
          lastQuickMenuItemObject = itmm;
        }
      });
    });

    // Check for corresponding question group object
    let lastQuestionGroupObject = false;
    LS.ld.each(questiongroups, function (itm) {
      const regTest = new RegExp('questionGroupsAdministration/view\\?surveyid=\\d*&gid=' + itm.gid + '|questionGroupsAdministration/edit\\?surveyid=\\d*&gid=' + itm.gid + '|questionGroupsAdministration/view/surveyid/\\d*/gid/' + itm.gid + '|questionGroupsAdministration/edit/surveyid/\\d*/gid/' + itm.gid);
      if (regTest.test(currentUrl) || LS.ld.endsWith(currentUrl, itm.link)) {
        lastQuestionGroupObject = itm;
        return false;
      }
    });

    // Check for corresponding question
    let lastQuestionObject = false;
    const questionIdInput = document.querySelector('#edit-question-form [name="question[qid]"]');
    if (questionIdInput !== null) {
      const questionId = questionIdInput.value;
      LS.ld.each(questiongroups, function (itm) {
        LS.ld.each(itm.questions, function (itmm) {
          if (questionId === itmm.qid) {
            lastQuestionObject = itmm;
            lastQuestionGroupObject = itm;
            return false;
          }
        });
        if (lastQuestionObject !== false) {
          return false;
        }
      });
    }

    // Unload every selection
    _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('closeAllMenus');
    if (lastMenuItemObject !== false && !_StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed')) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('lastMenuItemOpen', lastMenuItemObject);
    }
    if (lastQuickMenuItemObject !== false && _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed')) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('lastMenuItemOpen', lastQuickMenuItemObject);
    }
    if (lastQuestionGroupObject !== false) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('lastQuestionGroupOpen', lastQuestionGroupObject);
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('addToQuestionGroupOpenArray', lastQuestionGroupObject);
    }
    if (lastQuestionObject !== false) {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('lastQuestionOpen', lastQuestionObject);
    }
  }

  /**
   * Handle question group order change
   */
  function handleQuestionGroupOrderChange() {
    showLoader = true;
    render();
    const questiongroups = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('questiongroups');
    const surveyid = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('surveyid');
    _Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].updateQuestionGroupOrder(questiongroups, surveyid).then(function () {
      return _Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].getQuestions();
    }).then(function () {
      showLoader = false;
      render();
    }).catch(function (error) {
      console.ls.error('questiongroups updating error!', error);
      _Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].getQuestions().then(function () {
        showLoader = false;
        render();
      });
    });
  }

  /**
   * Bind event handlers
   */
  function bindEvents() {
    // Window resize
    window.addEventListener('resize', LS.ld.debounce(calculateHeight, 300));

    // Body mouse move for resize
    $('body').on('mousemove', handleMouseMove);

    // Custom events
    $(document).on('vue-sidemenu-update-link', controlActiveLink);
    $(document).on('vue-reload-remote', function () {
      _Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].getQuestions();
      _Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].collectMenus();
      _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].commit('newToggleKey');
    });
    $(document).on('vue-redraw', function () {
      _Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].getQuestions();
      _Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].collectMenus();
    });
    $(document).on('pjax:send', function () {
      if (_UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].useMobileView() && smallScreenHidden) {
        smallScreenHidden = false;
        render();
      }
    });
    $(document).on('pjax:refresh', controlActiveLink);

    // EventBus equivalent for updateSideBar
    $(document).on('updateSideBar', function (e, payload) {
      loading = true;
      renderContent();
      const promises = [Promise.resolve()];
      if (payload && payload.updateQuestions) {
        promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].getQuestions());
      }
      if (payload && payload.collectMenus) {
        promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].collectMenus());
      }
      if (payload && payload.activeMenuIndex) {
        controlActiveLink();
      }
      Promise.all(promises).catch(function (errors) {
        console.ls.error(errors);
      }).finally(function () {
        loading = false;
        renderContent();
      });
    });
  }

  /**
   * Render the sidebar HTML
   */
  function render() {
    if (!container) return;
    const isCollapsed = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isCollapsed');
    const currentTab = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('currentTab');
    const isRTL = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].getComputed('isRTL');
    const inSurveyViewHeight = _StateManager_js__WEBPACK_IMPORTED_MODULE_4__["default"].get('inSurveyViewHeight');
    const currentSidebarWidth = getSideBarWidth();
    let classes = 'd-flex col-lg-4 ls-ba position-relative transition-animate-width';
    if (smallScreenHidden) {
      classes += ' toggled';
    }
    const showMainContent = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].useMobileView() && smallScreenHidden || !_UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].useMobileView();
    const showPlaceholder = _UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].useMobileView() && smallScreenHidden;
    const showResizeOverlay = isMouseDown;
    let html = '<div id="sidebar" class="' + classes + '" style="width: ' + currentSidebarWidth + 'px; max-height: ' + inSurveyViewHeight + 'px; display: flex;">';
    if (showMainContent) {
      // Loader overlay
      if (showLoader) {
        html += '<div class="sidebar_loader" style="width: ' + getSideBarWidth() + 'px; height: ' + $('#sidebar').height() + 'px;">' + '<div class="ls-flex ls-flex-column fill align-content-center align-items-center">' + '<i class="ri-loader-2-fill remix-2x remix-spin"></i>' + '</div>' + '</div>';
      }
      html += '<div class="col-12 mainContentContainer">';
      html += '<div class="mainMenu col-12 position-relative">';

      // Sidebar state toggle (tabs)
      html += renderStateToggle(isCollapsed, currentTab, isRTL);

      // Side menu content
      html += '<div id="sidemenu-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'settings' ? 'block' : 'none') + '; min-height: ' + calculateSideBarMenuHeight() + ';"></div>';

      // Question explorer content
      html += '<div id="questionexplorer-container" class="slide-fade" style="display: ' + (!isCollapsed && currentTab === 'questiontree' ? 'block' : 'none') + '; min-height: ' + calculateSideBarMenuHeight() + ';"></div>';

      // Quick menu (collapsed state)
      html += '<div id="quickmenu-container" style="display: ' + (isCollapsed ? 'block' : 'none') + ';"></div>';

      // Resize handle
      if (_UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].useMobileView() && !smallScreenHidden || !_UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].useMobileView()) {
        html += '<div class="resize-handle ls-flex-column" style="height: ' + calculateSideBarMenuHeight() + ';">';
        if (!isCollapsed) {
          html += '<button class="btn resize-btn" type="button">' + '<svg width="9" height="14" viewBox="0 0 9 14" fill="none" xmlns="http://www.w3.org/2000/svg">' + '<path fill-rule="evenodd" clip-rule="evenodd" d="M0.4646 0.125H3.24762V2.625H0.4646V0.125ZM6.03064 0.125H8.81366V2.625H6.03064V0.125ZM0.4646 5.75H3.24762V8.25H0.4646V5.75ZM6.03064 5.75H8.81366V8.25H6.03064V5.75ZM0.4646 11.375H3.24762V13.875H0.4646V11.375ZM6.03064 11.375H8.81366V13.875H6.03064V11.375Z" fill="currentColor"/>' + '</svg>' + '</button>';
        }
        html += '</div>';
      }
      html += '</div>'; // .mainMenu
      html += '</div>'; // .mainContentContainer
    }

    // Placeholder for mobile
    if (showPlaceholder) {
      html += '<div class="scoped-placeholder-greyed-area"> </div>';
    }

    // Resize overlay to prevent mouse issues
    if (showResizeOverlay) {
      html += '<div style="position: fixed; inset: 0;"></div>';
    }
    html += '</div>'; // #sidebar

    container.innerHTML = html;

    // Bind internal events after render
    bindInternalEvents();

    // Render sub-components
    renderContent();
  }

  /**
   * Render state toggle (tabs)
   */
  function renderStateToggle(isCollapsed, currentTab, isRTL) {
    let html = '<div class="ls-space col-12">';
    html += '<div class="ls-flex-row align-content-space-between align-items-flex-end ls-space padding left-0 bottom-0 top-0">';
    if (!isCollapsed) {
      html += '<div class="ls-flex-item grow-10 col-12">' + '<ul class="nav nav-tabs" id="surveysystem" role="tablist">' + '<li class="nav-item">' + '<a id="adminsidepanel__sidebar--selectorSettingsButton" class="nav-link sidebar-tab-link' + (currentTab === 'settings' ? ' active' : '') + '" href="#settings" data-tab="settings" role="tab">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].translate('settings') + '</a>' + '</li>' + '<li class="nav-item">' + '<a id="adminsidepanel__sidebar--selectorStructureButton" class="nav-link sidebar-tab-link' + (currentTab === 'questiontree' ? ' active' : '') + '" href="#structure" data-tab="questiontree" role="tab">' + _UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].translate('structure') + '</a>' + '</li>' + '</ul>' + '</div>';
    } else {
      const arrowClass = isRTL ? 'ri-arrow-left-s-line' : 'ri-arrow-right-s-line';
      html += '<button class="btn btn-outline-secondary ls-space padding left-15 right-15 expand-sidebar-btn">' + '<i class="' + arrowClass + '"></i>' + '</button>';
    }
    html += '</div>';
    html += '</div>';
    return html;
  }

  /**
   * Render content for sub-components
   */
  function renderContent() {
    const sidemenuContainer = document.getElementById('sidemenu-container');
    const questionExplorerContainer = document.getElementById('questionexplorer-container');
    const quickmenuContainer = document.getElementById('quickmenu-container');
    if (sidemenuContainer) {
      _SideMenu_js__WEBPACK_IMPORTED_MODULE_7__["default"].render(sidemenuContainer, loading);
    }
    if (questionExplorerContainer) {
      _QuestionExplorer_js__WEBPACK_IMPORTED_MODULE_9__["default"].render(questionExplorerContainer, loading, handleQuestionGroupOrderChange);
    }
    if (quickmenuContainer) {
      _QuickMenu_js__WEBPACK_IMPORTED_MODULE_8__["default"].render(quickmenuContainer, loading);
    }

    // Re-initialize tooltips
    _UIHelpers_js__WEBPACK_IMPORTED_MODULE_6__["default"].redoTooltips();
  }

  /**
   * Bind internal events after render
   */
  function bindInternalEvents() {
    // Tab switching
    $(container).off('click', '.sidebar-tab-link').on('click', '.sidebar-tab-link', function (e) {
      e.preventDefault();
      const tab = $(this).data('tab');
      changeCurrentTab(tab);
    });

    // Expand button (collapsed state)
    $(container).off('click', '.expand-sidebar-btn').on('click', '.expand-sidebar-btn', function (e) {
      e.preventDefault();
      toggleCollapse();
    });

    // Resize handle
    $(container).off('mousedown', '.resize-btn').on('mousedown', '.resize-btn', handleMouseDown);
    $(container).off('mouseup').on('mouseup', handleMouseUp);
    $(container).off('mouseleave', '#sidebar').on('mouseleave', '#sidebar', handleMouseLeave);

    // Placeholder click (mobile)
    $(container).off('click', '.scoped-placeholder-greyed-area').on('click', '.scoped-placeholder-greyed-area', toggleSmallScreenHide);
  }

  /**
   * Update sidebar (called externally)
   */
  function update(options) {
    options = options || {};
    loading = true;
    renderContent();
    const promises = [];
    if (options.updateQuestions) {
      promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].getQuestions());
    }
    if (options.collectMenus) {
      promises.push(_Actions_js__WEBPACK_IMPORTED_MODULE_5__["default"].collectMenus());
    }
    Promise.all(promises).then(function () {
      if (options.activeMenuIndex) {
        controlActiveLink();
      }
    }).catch(function (error) {
      console.ls.error(error);
    }).finally(function () {
      loading = false;
      renderContent();
    });
  }
  return {
    init: init,
    render: render,
    update: update,
    toggleCollapse: toggleCollapse,
    controlActiveLink: controlActiveLink
  };
}();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Sidebar);

/***/ }),

/***/ "./src/stateConfig.js":
/*!****************************!*\
  !*** ./src/stateConfig.js ***!
  \****************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createDefaultState: () => (/* binding */ createDefaultState),
/* harmony export */   createGetters: () => (/* binding */ createGetters),
/* harmony export */   createMutations: () => (/* binding */ createMutations)
/* harmony export */ });
/**
 * State configuration for adminsidepanel
 * Defines default state, mutations, and getters
 */

/**
 * Create default state
 * @param {string|number} userid
 * @param {string|number} surveyid
 * @returns {Object}
 */
function createDefaultState(userid, surveyid) {
  // Calculate default sidebar width
  let sidebarWidth = $('#vue-apps-main-container').width() * 0.33;
  if ($('#vue-apps-main-container').width() > 1400) {
    sidebarWidth = $('#vue-apps-main-container').width() * 0.25;
  }
  return {
    surveyid: 0,
    language: '',
    maxHeight: 0,
    inSurveyViewHeight: 1000,
    sideBodyHeight: '100%',
    sideBarHeight: 400,
    currentUser: userid,
    currentTab: 'settings',
    sidebarwidth: sidebarWidth,
    maximalSidebar: false,
    isCollapsed: false,
    pjax: null,
    pjaxLoading: false,
    lastMenuOpen: false,
    lastMenuItemOpen: false,
    lastQuestionOpen: false,
    lastQuestionGroupOpen: false,
    questionGroupOpenArray: [],
    questiongroups: [],
    collapsedmenus: null,
    sidemenus: null,
    topmenus: null,
    bottommenus: null,
    surveyActiveState: false,
    toggleKey: Math.floor(Math.random() * 10000) + '--key',
    allowOrganizer: true
  };
}

/**
 * Create mutations for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
function createMutations(StateManager) {
  return {
    updateSurveyId: function (newSurveyId) {
      StateManager.set('surveyid', newSurveyId);
    },
    changeLanguage: function (language) {
      StateManager.set('language', language);
    },
    changeCurrentTab: function (value) {
      StateManager.set('currentTab', value);
    },
    changeSidebarwidth: function (value) {
      StateManager.set('sidebarwidth', value);
    },
    maxSideBarWidth: function (value) {
      StateManager.set('maximalSidebar', value);
    },
    changeIsCollapsed: function (value) {
      StateManager.set('isCollapsed', value);
      $(document).trigger('vue-sidemenu-update-link');
    },
    changeMaxHeight: function (newHeight) {
      StateManager.set('maxHeight', newHeight);
    },
    changeSideBarHeight: function (newHeight) {
      StateManager.set('sideBarHeight', newHeight);
    },
    changeInSurveyViewHeight: function (newHeight) {
      StateManager.set('inSurveyViewHeight', newHeight);
    },
    changeSideBodyHeight: function (newHeight) {
      StateManager.set('sideBodyHeight', newHeight ? newHeight + 'px' : '100%');
    },
    changeCurrentUser: function (newUser) {
      StateManager.set('currentUser', newUser);
    },
    closeAllMenus: function () {
      StateManager.set('lastMenuOpen', false);
      StateManager.set('lastMenuItemOpen', false);
      StateManager.set('lastQuestionGroupOpen', false);
      StateManager.set('lastQuestionOpen', false);
    },
    lastMenuItemOpen: function (menuItem) {
      StateManager.set('lastMenuOpen', menuItem.menu_id);
      StateManager.set('lastMenuItemOpen', menuItem.id);
      StateManager.set('lastQuestionGroupOpen', false);
      StateManager.set('lastQuestionOpen', false);
    },
    lastMenuOpen: function (menuObject) {
      StateManager.set('lastMenuOpen', menuObject.id);
      StateManager.set('lastQuestionOpen', false);
      StateManager.set('lastMenuItemOpen', false);
    },
    lastQuestionOpen: function (questionObject) {
      StateManager.set('lastQuestionGroupOpen', questionObject.gid);
      StateManager.set('lastQuestionOpen', questionObject.qid);
      StateManager.set('lastMenuItemOpen', false);
    },
    lastQuestionGroupOpen: function (questionGroupObject) {
      StateManager.set('lastQuestionGroupOpen', questionGroupObject.gid);
      StateManager.set('lastQuestionOpen', false);
    },
    questionGroupOpenArray: function (questionGroupOpenArray) {
      StateManager.set('questionGroupOpenArray', questionGroupOpenArray);
    },
    updateQuestiongroups: function (questiongroups) {
      StateManager.set('questiongroups', questiongroups);
    },
    addToQuestionGroupOpenArray: function (questiongroupToAdd) {
      const state = StateManager.get();
      const tmpArray = state.questionGroupOpenArray.slice();
      tmpArray.push(questiongroupToAdd.gid);
      StateManager.set('questionGroupOpenArray', tmpArray);
    },
    updateSidemenus: function (sidemenus) {
      StateManager.set('sidemenus', sidemenus);
    },
    updateCollapsedmenus: function (collapsedmenus) {
      StateManager.set('collapsedmenus', collapsedmenus);
    },
    updateTopmenus: function (topmenus) {
      StateManager.set('topmenus', topmenus);
    },
    updateBottommenus: function (bottommenus) {
      StateManager.set('bottommenus', bottommenus);
    },
    setSurveyActiveState: function (surveyState) {
      StateManager.set('surveyActiveState', !!surveyState);
    },
    newToggleKey: function () {
      StateManager.set('toggleKey', Math.floor(Math.random() * 10000) + '--key');
    },
    setAllowOrganizer: function (newVal) {
      StateManager.set('allowOrganizer', newVal);
    }
  };
}

/**
 * Create getters for StateManager
 * @param {Object} StateManager - StateManager instance
 * @returns {Object}
 */
function createGetters(StateManager) {
  return {
    substractContainer: function () {
      const state = StateManager.get();
      const bodyWidth = (1 - parseInt(state.sidebarwidth) / $('#vue-apps-main-container').width()) * 100;
      const collapsedBodyWidth = (1 - parseInt('98px') / $('#vue-apps-main-container').width()) * 100;
      return Math.floor(state.isCollapsed ? collapsedBodyWidth : bodyWidth) + '%';
    },
    sideBarSize: function () {
      const state = StateManager.get();
      const sidebarWidth = parseInt(state.sidebarwidth) / $('#vue-apps-main-container').width() * 100;
      const collapsedSidebarWidth = parseInt(98) / $('#vue-apps-main-container').width() * 100;
      return Math.ceil(state.isCollapsed ? collapsedSidebarWidth : sidebarWidth) + '%';
    },
    isRTL: function () {
      return document.getElementsByTagName('html')[0].getAttribute('dir') === 'rtl';
    },
    isCollapsed: function () {
      if (window.innerWidth < 768) {
        return false;
      }
      const state = StateManager.get();
      return state.isCollapsed;
    }
  };
}

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/global */
/******/ 	(() => {
/******/ 		__webpack_require__.g = (function() {
/******/ 			if (typeof globalThis === 'object') return globalThis;
/******/ 			try {
/******/ 				return this || new Function('return this')();
/******/ 			} catch (e) {
/******/ 				if (typeof window === 'object') return window;
/******/ 			}
/******/ 		})();
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other entry modules.
(() => {
/*!*******************************!*\
  !*** ./lib/surveysettings.js ***!
  \*******************************/
$('#copysurveyform').submit(copysurvey);
function setAdministratorFieldsVisibility(form) {
  var option = form.find("[name=administrator]:checked").val();
  var fieldsContainer = $("#conditional-administrator-fields");
  if (option == "custom") {
    fieldsContainer.show(200);
  } else {
    fieldsContainer.hide(200);
  }
}
$(document).on('click', '[data-copy] :submit', function () {
  $('form :input[value=\'' + $(this).val() + '\']').click();
});
// $(document).on('submit',"#addnewsurvey",function(){
//     $('#addnewsurvey').attr('action',$('#addnewsurvey').attr('action')+location.hash);// Maybe validate before ?
// });
$(document).on('ready  pjax:scriptcomplete', function () {
  $('#template').on('change keyup', function (event) {
    console.ls.log('TEMPLATECHANGE', event);
    templatechange($(this));
  });
  $('[data-copy]').each(function () {
    $(this).html($('#' + $(this).data('copy')).html());
  });
  var jsonUrl = jsonUrl || null;
  $('#tabs').on('tabsactivate', function (event, ui) {
    if (ui.newTab.index() > 4)
      // Hide on import and copy tab, otherwise show
      {
        $('#btnSave').hide();
      } else {
      $('#btnSave').show();
    }
  });

  // If on "Create survey" form
  if ($('#addnewsurvey')) {
    var form = $('#addnewsurvey');

    // Set initial visibility
    setAdministratorFieldsVisibility(form);

    // Update visibility when 'administrator' option changes
    form.find("[name=administrator]").on('change', function () {
      setAdministratorFieldsVisibility(form);
    });
  }
});
function templatechange($element) {
  $('#preview-image-container').html('<div style="height:200px;" class="ls-flex ls-flex-column align-content-center align-items-center"><i class="ri-loader-2-fill remix-spin remix-3x"></i></div>');
  let templateName = $element.val();
  if (templateName === 'inherit') {
    templateName = $element.data('inherit-template-name');
  }
  $.ajax({
    url: $element.data('updateurl'),
    data: {
      templatename: templateName
    },
    method: 'POST',
    dataType: 'json',
    success: function (data) {
      $('#preview-image-container').html(data.image);
    },
    error: console.ls.error
  });
}
function copysurvey() {
  let sMessage = '';
  if ($('#copysurveylist').val() == '') {
    sMessage = sMessage + sSelectASurveyMessage;
  }
  if ($('#copysurveyname').val() == '') {
    sMessage = sMessage + '\n\r' + sSelectASurveyName;
  }
  if (sMessage != '') {
    alert(sMessage);
    return false;
  }
}
function in_array(needle, haystack, argStrict) {
  var key = '',
    strict = !!argStrict;
  if (strict) {
    for (key in haystack) {
      if (haystack[key] === needle) {
        return true;
      }
    }
  } else {
    for (key in haystack) {
      if (haystack[key] == needle) {
        return true;
      }
    }
  }
  return false;
}
function guidGenerator() {
  var S4 = function () {
    return ((1 + Math.random()) * 0x10000 | 0).toString(16).substring(1);
  };
  return S4() + S4() + '-' + S4() + '-' + S4() + '-' + S4() + '-' + S4() + S4() + S4();
}
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
var __webpack_exports__ = {};
/*!***********************************!*\
  !*** ./src/adminsidepanelmain.js ***!
  \***********************************/
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _StateManager_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./StateManager.js */ "./src/StateManager.js");
/* harmony import */ var _stateConfig_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./stateConfig.js */ "./src/stateConfig.js");
/* harmony import */ var _Actions_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./Actions.js */ "./src/Actions.js");
/* harmony import */ var _components_Sidebar_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./components/Sidebar.js */ "./src/components/Sidebar.js");
/**
 * AdminSidePanel - Main entry point (vanilla JS)
 * Replaces Vue-based adminsidepanelmain.js
 */





/**
 * Main AdminSidePanel factory function
 * @param {string|number} userid
 * @param {string|number} surveyid
 * @returns {Function}
 */
const Lsadminsidepanel = function (userid, surveyid) {
  'use strict';

  const panelNameSpace = {};

  /**
   * Apply survey ID to state
   */
  function applySurveyId() {
    if (surveyid !== 0 && surveyid !== '0' && surveyid !== 'newSurvey') {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('updateSurveyId', surveyid);
    }
  }

  /**
   * Control window size and adjust layout
   */
  function controlWindowSize() {
    const adminmenuHeight = $('body').find('nav').first().height() || 0;
    const footerHeight = $('body').find('footer').last().height() || 0;
    const menuHeight = $('.menubar').outerHeight() || 0;
    const inSurveyOffset = adminmenuHeight + footerHeight + menuHeight + 25;
    const windowHeight = window.innerHeight;
    const inSurveyViewHeight = windowHeight - inSurveyOffset;
    const sidebarWidth = $('#sidebar').width() || 0;
    const containerWidth = $('#vue-apps-main-container').width() || 1;
    const bodyWidth = (1 - parseInt(sidebarWidth) / containerWidth) * 100;
    const collapsedBodyWidth = (1 - parseInt('98px') / containerWidth) * 100;
    const inSurveyViewWidth = Math.floor($('#sidebar').data('collapsed') ? bodyWidth : collapsedBodyWidth) + '%';
    panelNameSpace.surveyViewHeight = inSurveyViewHeight;
    panelNameSpace.surveyViewWidth = inSurveyViewWidth;
    $('#fullbody-container').css({
      'max-width': inSurveyViewWidth,
      'overflow-x': 'auto'
    });
  }

  /**
   * Create the side menu
   */
  function createSideMenu() {
    const containerEl = document.getElementById('vue-sidebar-container');
    if (!containerEl) return null;

    // Initialize state manager with unified API
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].init({
      storagePrefix: 'limesurveyadminsidepanel',
      userid: userid,
      surveyid: surveyid,
      defaultState: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createDefaultState)(userid, surveyid),
      mutations: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createMutations)(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"]),
      getters: (0,_stateConfig_js__WEBPACK_IMPORTED_MODULE_1__.createGetters)(_StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"])
    });

    // Apply survey ID
    applySurveyId();

    // Set max height
    const maxHeight = $('#in_survey_common').height() - 35 || 400;
    _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('changeMaxHeight', maxHeight);

    // Set allow organizer - default to unlocked (1) unless explicitly locked
    if (window.SideMenuData && window.SideMenuData.allowOrganizer !== undefined) {
      // Only lock if explicitly set to 0, otherwise keep unlocked
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('setAllowOrganizer', window.SideMenuData.allowOrganizer === 0 ? 0 : 1);
    } else {
      // No server value, default to unlocked
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('setAllowOrganizer', 1);
    }

    // Initialize sidebar component
    _components_Sidebar_js__WEBPACK_IMPORTED_MODULE_3__["default"].init(containerEl);

    // Bind Vue-style events
    $(document).on('vue-redraw', function () {
      _StateManager_js__WEBPACK_IMPORTED_MODULE_0__["default"].commit('newToggleKey');
    });
    $(document).trigger('vue-reload-remote');
    return _components_Sidebar_js__WEBPACK_IMPORTED_MODULE_3__["default"];
  }

  /**
   * Apply Pjax methods for AJAX navigation
   */
  function applyPjaxMethods() {
    panelNameSpace.reloadcounter = 5;
    $(document).off('pjax:send.panelloading').on('pjax:send.panelloading', function () {
      $('<div id="pjaxClickInhibitor"></div>').appendTo('body');
      $('.ui-dialog.ui-corner-all.ui-widget.ui-widget-content.ui-front.ui-draggable.ui-resizable').remove();
      $('#pjax-file-load-container').find('div').css({
        width: '20%',
        display: 'block'
      });
      LS.adminsidepanel.reloadcounter--;
    });
    $(document).off('pjax:error.panelloading').on('pjax:error.panelloading', function (event) {
      if (console.ls && console.ls.log) {
        console.ls.log(event);
      }
    });
    $(document).off('pjax:complete.panelloading').on('pjax:complete.panelloading', function () {
      if (LS.adminsidepanel.reloadcounter === 0) {
        location.reload();
      }
    });
    $(document).off('pjax:scriptcomplete.panelloading').on('pjax:scriptcomplete.panelloading', function () {
      $('#pjax-file-load-container').find('div').css('width', '100%');
      $('#pjaxClickInhibitor').fadeOut(400, function () {
        $(this).remove();
      });
      $(document).trigger('vue-resize-height');
      $(document).trigger('vue-reload-remote');
      setTimeout(function () {
        $('#pjax-file-load-container').find('div').css({
          width: '0%',
          display: 'none'
        });
      }, 2200);
    });
  }

  /**
   * Create panel appliance
   */
  function createPanelAppliance() {
    // Initialize singleton Pjax
    if (window.singletonPjax) {
      window.singletonPjax();
    }

    // Create side menu
    if (document.getElementById('vue-sidebar-container')) {
      panelNameSpace.sidemenu = createSideMenu();
    }

    // Pagination click handler
    $(document).on('click', 'ul.pagination>li>a', function () {
      $(document).trigger('pjax:refresh');
    });

    // Window resize handling
    controlWindowSize();
    window.addEventListener('resize', LS.ld.debounce(controlWindowSize, 300));
    $(document).on('vue-resize-height', LS.ld.debounce(controlWindowSize, 300));

    // Apply Pjax methods
    applyPjaxMethods();
  }

  // Add to LS admin namespace
  if (LS && LS.adminCore && LS.adminCore.addToNamespace) {
    LS.adminCore.addToNamespace(panelNameSpace, 'adminsidepanel');
  }
  return createPanelAppliance;
};

// Document ready handler
$(document).ready(function () {
  let surveyid = 'newSurvey';
  if (window.LS !== undefined) {
    surveyid = window.LS.parameters.$GET.surveyid || window.LS.parameters.keyValuePairs.surveyid;
  }
  if (window.SideMenuData) {
    surveyid = window.SideMenuData.surveyid;
  }
  window.adminsidepanel = window.adminsidepanel || Lsadminsidepanel(window.LS.globalUserId, surveyid);
  window.adminsidepanel();
});
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Lsadminsidepanel);
})();

// This entry needs to be wrapped in an IIFE because it needs to be in strict mode.
(() => {
"use strict";
/*!**************************************!*\
  !*** ./scss/adminsidepanelmain.scss ***!
  \**************************************/
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin

})();

/******/ })()
;
//# sourceMappingURL=adminsidepanel.js.map